<?php

namespace shagabutdinov\moka;

class Call
{
    private $_stub = null;

    private $_with = null;
    private $_at = null;
    private $_on = null;
    private $_calls = null;

    public function __construct($stub)
    {
        $this->_stub = $stub;
    }

    public function returns($value)
    {
        if (!empty($this->_calls)) {
            throw new Error('can not use "returns()" and "calls()" together');
        }

        $this->_returns = $value;
        return $this;
    }

    public function with()
    {
        $this->_with = func_get_args();
        return $this;
    }

    public function at($time)
    {
        $this->_at = $time;
        return $this;
    }

    public function on($callback)
    {
        $this->_on = $callback;
        return $this;
    }

    public function of($instanceNumber)
    {
        $this->_of = $instanceNumber;
        return $this;
    }

    public function calls($callback)
    {
        if (!empty($this->_returns)) {
            throw new Error('can not use "calls()" and "returns()" together');
        }

        $this->_calls = $callback;
        return $this;
    }

    public function invoke($args)
    {
        if ($this->_with !== null && $this->_with !== $args) {
            return [false, null];
        }

        if ($this->_at !== null && $this->_at !== count($this->_stub->calls())) {
            return [false, null];
        }

        if ($this->_on !== null && !call_user_func_array($this->_on, [])) {
            return [false, null];
        }

        if ($this->_calls !== null) {
            return [true, call_user_func_array($this->_calls, [])];
        }

        return [true, $this->_returns];
    }
}
