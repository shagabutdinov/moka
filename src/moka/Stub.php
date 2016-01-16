<?php

namespace shagabutdinov\moka;

class Stub
{
    private $_stubs = [];
    private $_calls = [];
    private $_method = null;

    public function __construct($method)
    {
        $this->_method = $method;
    }

    public function add($stub)
    {
        $this->_stubs[] = $stub;
    }

    public function invoke($args)
    {
        foreach (array_reverse($this->_stubs) as $stub) {
            list($invoked, $result) = $stub->invoke($args);
            if ($invoked) {
                $this->_calls[] = $args;
                return $result;
            }
        }

        throw new Error('can not find valid stub for method "' .
            $this->_method . '"');
    }

    public function calls()
    {
        return $this->_calls;
    }
}
