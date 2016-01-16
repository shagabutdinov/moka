<?php

namespace shagabutdinov;

class Moka
{
    private static $_counter = 0;

    public static function spy($result = '__UNDEFINED__')
    {
        return moka\Spy::factory($result);
    }

    public static function stub($parent = null, $methods = [])
    {
        $class = self::stubClass($parent, $methods);
        $result = new $class();
        return $result;
    }

    public static function mock($parent = null, $args = [], $methods = [])
    {
        $class = self::mockClass($parent, $methods);
        $reflection = new \ReflectionClass($class);
        $result = $reflection->newInstanceArgs($args);
        return $result;
    }

    public static function stubClass($parent = null, $methods = [])
    {
        return self::_create($parent, $methods, true);
    }

    public static function mockClass($parent = null, $methods = [])
    {
        return self::_create($parent, $methods, false);
    }

    private static function _create($parent, $methods, $isStub)
    {
        $name = '__shagabutdinov_moka_' . static::$_counter . '';
        static::$_counter += 1;

        $classCode = self::_createClassCode($name, $parent, $methods, $isStub);
        eval($classCode);

        $name::$moka = moka\Stubs::factory();
        $name::$__mokaValues = $methods;
        self::_setStaticMokaValues($name::$moka, $methods);

        return $name;
    }

    private static function _setStaticMokaValues($moka, $values)
    {
        foreach ($values as $key => $value) {
            if (is_numeric($key)) {
                continue;
            }

            if (strpos($key, '::') !== 0) {
                continue;
            }

            $moka->stubs(mb_substr($key, 2))->returns($value);
        }
    }

    public static function __setMokaValues($moka, $values)
    {
        foreach ($values as $key => $value) {
            if (is_numeric($key)) {
                continue;
            }

            if (strpos($key, '::') === 0) {
                continue;
            }

            if ($key === '__construct') {
                continue;
            }

            $moka->stubs($key)->returns($value);
        }
    }

    private static function _createClassCode($name, $parent, $methods, $isStub)
    {
        $classCode = self::_createHeader($name, $parent);

        $reflection = null;
        if (!empty($parent)) {
            $reflection = new \ReflectionClass($parent);
        }

        $classCode .= self::_createConstructor($reflection, $methods);
        $classCode .= self::_createMethods($reflection, $methods, $isStub);

        $classCode .= "\n}";
        return $classCode;
    }

    private static function _createHeader($name, $parent)
    {
        $classCode = 'class ' . $name;

        if (!empty($parent)) {
            $classCode .= ' extends ' . $parent;
        }

        $classCode .= "\n{\n";
        $classCode .= <<<DEFINITION
    public static \$moka = null;
    public static \$__mokaValues = null;

    public \$__moka = null;
DEFINITION;

        return $classCode;
    }

    private static function _createMethods($reflection, $methods, $isStub)
    {
        $result = '';
        $methods = self::_createInitialMethods($methods);

        if ($reflection !== null) {
            foreach ($reflection->getMethods() as $method) {
                if (!$method->isPublic() || $method->isFinal()) {
                    continue;
                }

                $name = $method->getName();
                if ($method->isStatic()) {
                    $name = '::' . $name;
                }

                $args = self::_createMethodArguments($method);

                if (isset($methods[$name]) || $isStub) {
                    $methods[$name] = [$name, implode(', ', $args)];
                }
            }
        }

        foreach ($methods as $method) {
            list($name, $args) = $method;
            if ($name === '__construct') {
                continue;
            }

            if (strpos($name, '::') === 0) {
                $name = mb_substr($name, 2);
                $result .= <<<METHOD


    public static function $name($args)
    {
        return call_user_func_array(
            [self::\$moka, 'invoke'],
            ['$name', func_get_args()]
        );
    }
METHOD;
            } else {
                $result .= <<<METHOD


    public function $name($args)
    {
        return call_user_func_array(
            [\$this->__moka, 'invoke'],
            ['$name', func_get_args()]
        );
    }
METHOD;
            }

        }

        return $result;
    }

    private static function _createInitialMethods($methods)
    {
        $result = [];
        foreach ($methods as $index => $value) {
            if (is_numeric($index)) {
                $result[$value] = [$value, ''];
            } else {
                $result[$index] = [$index, ''];
            }
        }

        return $result;
    }

    private static function _createMethodArguments($method)
    {
        $parameters = $method->getParameters();
        $args = [];
        foreach ($parameters as $parameter) {
            $arg = '$' . $parameter->getName();
            if ($parameter->isPassedByReference()) {
                $arg = '&' . $arg;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arg .= ' = ' . var_export(
                    $parameter->getDefaultValue(),
                    true
                );
            }

            // php 7
            if (method_exists($parameter, 'hasType') && $parameter->hasType()) {
                $arg = $parameter->getType() . ' ' . $arg;
            } else { // php <7
                if ($parameter->isArray()) {
                    $arg = 'array ' . $arg;
                } elseif ($parameter->isCallable()) {
                    $arg = 'callable ' . $arg;
                }
            }

            $args[] = $arg;
        }

        return $args;
    }

    private static function _createConstructor($reflection, $methods)
    {
        $construct = '';

        $hasConstructor = (
            in_array('__construct', $methods) ||
            array_key_exists('__construct', $methods)
        );

        $parentHasConstructor = (
            $reflection !== null &&
            $reflection->hasMethod('__construct')
        );

        if ($hasConstructor) {
            $construct = <<<CONSTRUCTOR

        \$this->__moka->stubs('__construct')->returns(null);
        call_user_func_array(
            [\$this->__moka, 'invoke'],
            ['__construct', func_get_args()]
        );
CONSTRUCTOR;
        } elseif ($parentHasConstructor) {
            $construct = <<<CONSTRUCTOR

        call_user_func_array(
            array(\$this, 'parent::__construct'),
            func_get_args()
        );
CONSTRUCTOR;
        }

        $result = <<<CONSTRUCTOR

    public function __construct()
    {
        self::\$moka->__addInstance(\$this);
        \$this->__moka = \shagabutdinov\moka\Stubs::factory();
        \shagabutdinov\Moka::__setMokaValues(\$this->__moka, self::\$__mokaValues);
        $construct
    }

    public function moka()
    {
        return \$this->__moka;
    }

CONSTRUCTOR;

        return $result;
    }
}
