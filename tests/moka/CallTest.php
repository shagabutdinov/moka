<?php

namespace shagabutdinov\moka;

class CallTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->_call = new Call(new CallTestStub([]), 'METHOD');
    }

    // returns

    public function testReturnsReturnsCall()
    {
        $this->assertInstanceOf(
            'shagabutdinov\moka\Call',
            $this->_call->returns(null)
        );
    }

    public function testInvokeReturnsValue()
    {
        $result = $this->_call->returns('VALUE')->invoke([]);
        $this->assertEquals([true, 'VALUE'], $result);
    }

    // with

    public function testWithReturnsCall()
    {
        $this->assertInstanceOf(
            'shagabutdinov\moka\Call',
            $this->_call->with('VALUE')
        );
    }

    public function testInvokeWithReturnsValue()
    {
        $result = $this->_call->with('ARG')->returns('VALUE')->invoke(['ARG']);
        $this->assertEquals([true, 'VALUE'], $result);
    }

    public function testInvokeWithNotReturnsValue()
    {
        $result = $this->_call->with('WRONG')->returns('VALUE')->invoke([]);
        $this->assertEquals([false, null], $result);
    }

    // at

    public function testAtReturnsCall()
    {
        $this->assertInstanceOf('shagabutdinov\moka\Call', $this->_call->at(0));
    }

    public function testInvokeWithAtReturnsValue()
    {

        $result = $this->_call->at(0)->returns('VALUE')->invoke([]);
        $this->assertEquals([true, 'VALUE'], $result);
    }

    public function testInvokeWithAtNotReturnsValue()
    {
        $result = $this->_call->at(1)->returns('VALUE')->invoke([]);
        $this->assertEquals([false, null], $result);
    }

    // on

    public function testOnReturnsCall()
    {
        $callback = function () {
            return true;
        };

        $this->assertInstanceOf(
            'shagabutdinov\moka\Call',
            $this->_call->on($callback)
        );
    }

    public function testInvokeWithOnReturnsValue()
    {
        $callback = function () {
            return true;
        };

        $result = $this->_call->on($callback)->returns('VALUE')->invoke([]);
        $this->assertEquals([true, 'VALUE'], $result);
    }

    public function testInvokeWithOnNotReturnsValue()
    {
        $result = $this->_call->on(function () {
            return false;
        })->
            returns('VALUE')->invoke([]);
        $this->assertEquals([false, null], $result);
    }

    // calls

    public function testCallReturnsCall()
    {
        $this->assertInstanceOf(
            'shagabutdinov\moka\Call',
            $this->_call->calls(function () {
                return true;
            })
        );
    }

    public function testInvokeWithCallReturnsValue()
    {
        $result = $this->_call->calls(function () {
            return 'RES';

        })->invoke([]);
        $this->assertEquals([true, 'RES'], $result);
    }

    public function testInvokeWithCallAndReturnsThrowError()
    {
        $this->setExpectedException(
            '\shagabutdinov\moka\Error',
            'can not use "returns()" and "calls()" together'
        );

        $callback = function () {

        };

        $result = $this->_call->calls($callback)->returns('RESULT');
    }

    public function testInvokeWithReturnsAndCallThrowError()
    {
        $this->setExpectedException(
            '\shagabutdinov\moka\Error',
            'can not use "calls()" and "returns()" together'
        );

        $callback = function () {

        };

        $result = $this->_call->returns('RESULT')->calls($callback);
    }
}


class CallTestStub
{

    public $calls = null;

    public function __construct($calls)
    {
        $this->calls = $calls;
    }

    public function calls()
    {
        return $this->calls;
    }
}
