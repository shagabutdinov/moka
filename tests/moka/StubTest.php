<?php

namespace shagabutdinov\moka;

class StubTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_object = new Stub('METHOD');
    }

    public function testInvokeThrowsErrorIfNoStubsAdded()
    {
        $this->setExpectedException(
            '\shagabutdinov\moka\Error',
            'can not find valid stub for method "METHOD"'
        );

        $this->_object->invoke([]);
    }

    public function testInvokeThrowsErrorIfNoValidStubsFound()
    {
        $this->setExpectedException(
            '\shagabutdinov\moka\Error',
            'can not find valid stub for method "METHOD"'
        );

        $this->_object->add(new StubTestCallStub(false));
        $this->_object->invoke([]);
    }

    public function testInvokeReturnsResultIfValidStubFound()
    {
        $this->_object->add(new StubTestCallStub(true, 'RESULT'));
        $this->assertEquals('RESULT', $this->_object->invoke([]));
    }

    public function testInvokeReturnsLastAssignedResultIfValidStubFound()
    {
        $this->_object->add(new StubTestCallStub(true, 'WRONG'));
        $this->_object->add(new StubTestCallStub(true, 'RESULT'));
        $this->assertEquals('RESULT', $this->_object->invoke([]));
    }


    public function testInvokeReturnsCallArgs()
    {
        $this->_object->add(new StubTestCallStub(true, 'RESULT'));
        $this->_object->invoke(['ARG']);
        $this->assertEquals(['ARG'], $this->_object->calls()[0]);
    }
}

class StubTestCallStub
{
    public function __construct($found, $result = null)
    {
        $this->_found = $found;
        $this->_result = $result;
    }

    public function invoke()
    {
        return [$this->_found, $this->_result];
    }
}
