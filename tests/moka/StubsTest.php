<?php

namespace shagabutdinov\moka;

class StubsTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->_stub = new \shagabutdinov\moka\StubsTestStub();
        $this->_call = new \shagabutdinov\moka\StubsTestCall();

        $this->_object = new Stubs([
            'create_stub_class' => function ($method) {
                $this->_stub->method = $method;
                return $this->_stub;
            },
            'create_call_class' => function ($stub) {
                $this->_call->stub = $stub;
                return $this->_call;
            },
        ]);
    }

    public function testStubsCreatesStubObjectWithMethodName()
    {
        $this->_object->stubs('METHOD');
        $this->assertEquals('METHOD', $this->_stub->method);
    }

    public function testStubsCallsAddMethod()
    {
        $this->_object->stubs('METHOD');
        $this->assertInstanceOf(
            'shagabutdinov\moka\StubsTestCall',
            $this->_stub->add
        );
    }

    public function testStubsCreateCallObjectWithMethod()
    {
        $this->_object->stubs('METHOD');
        $this->assertEquals($this->_stub, $this->_stub->add->stub);
    }

    public function testInvokeThrowsIfNoStubDefined()
    {
        $this->setExpectedException(
            '\shagabutdinov\moka\Error',
            'method "METHOD" is not stubbed'
        );

        $this->_object->invoke('METHOD', []);
    }

    public function testInvokeInvokesStubWithArgs()
    {
        $this->_object->stubs('METHOD');
        $this->_object->invoke('METHOD', ['ARG']);
        $this->assertEquals(['ARG'], $this->_stub->invoke);
    }

    public function testGetInstanceThrowsError()
    {
        $this->setExpectedException(
            '\shagabutdinov\moka\Error',
            'instance #0 was not created'
        );

        $this->_object->instance();
    }

    public function testGetInstanceReturnsInstance()
    {
        $this->_object->__addInstance('INSTANCE');
        $this->assertEquals('INSTANCE', $this->_object->instance());
    }
}

class StubsTestStub
{
    public $method;
    public $add;
    public $invoke;

    public function __construct()
    {
    }

    public function add($call)
    {
        $this->add = $call;
    }

    public function invoke($args)
    {
        $this->invoke = $args;
    }

    public function report()
    {
        return 'REPORT';
    }

    public function calls()
    {
        return 'CALLS';
    }
}

class StubsTestCall
{
    public $stub;
}
