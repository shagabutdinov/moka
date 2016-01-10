<?php

namespace shagabutdinov\moka;

class Stubs
{
    private $_stubs = [];
    private $_instances = [];

    private $_createStubClass = null;
    private $_createCallClass = null;

    public static function factory()
    {
        return new self([
            'create_stub_class' => function($method) {
                return new \shagabutdinov\moka\Stub($method);
            },
            'create_call_class' => function($stub) {
                return new \shagabutdinov\moka\Call($stub);
            },
        ]);
    }

    public function __construct($options = [])
    {
        $this->_createStubClass = $options['create_stub_class'];
        $this->_createCallClass = $options['create_call_class'];
    }

    public function stubs($method)
    {
        if(empty($this->_stubs[$method])) {
            $this->_stubs[$method] = ($this->_createStubClass)($method);
        }

        $call = ($this->_createCallClass)($this->_stubs[$method]);
        $this->_stubs[$method]->add($call);
        return $call;
    }


    public function report($method)
    {
        if(empty($this->_stubs[$method])) {
            return null;
        }

        return $this->_stubs[$method]->calls();
    }

    public function invoke($method, $args)
    {
        if(empty($this->_stubs[$method])) {
            throw new Error('method "' . $method . '" is not stubbed');
        }

        return $this->_stubs[$method]->invoke($args);
    }

    public function __addInstance($instance)
    {
        $this->_instances[] = $instance;
    }

    public function instance($number = 0)
    {
        if(empty($this->_instances[$number])) {
            throw new Error('instance #' . $number . ' was not created');
        }

        return $this->_instances[$number];
    }

}