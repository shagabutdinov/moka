<?php

namespace shagabutdinov\moka;

class Spy
{

    public static function factory($result = '__UNDEFINED__')
    {
        return new self($result, ['stubs' => Stubs::factory()]);
    }

    public function __construct($result = '__UNDEFINED__', $options = [])
    {
        $this->_stubs = $options['stubs'];

        if ($result !== '__UNDEFINED__') {
            $this->stubs()->returns($result);
        }
    }

    public function report()
    {
        return $this->_stubs->report('invoke');
    }

    public function stubs()
    {
        return $this->_stubs->stubs('invoke');
    }

    public function __invoke()
    {
        return call_user_func_array(
            [$this->_stubs, 'invoke'],
            ['invoke', func_get_args()]
        );
    }
}
