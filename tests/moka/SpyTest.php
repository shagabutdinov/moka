<?php

namespace shagabutdinov\moka;

class SpyTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->_stubs = \shagabutdinov\Moka::stub(null, [
            'report' => 'REPORT',
            'stubs' => 'STUBS',
            'invoke' => 'INVOKE',
        ]);

        $this->_object = new Spy('__UNDEFINED__', ['stubs' => $this->_stubs]);
    }

    public function testStubReturnsInvokeResult()
    {
        $this->assertEquals('STUBS', $this->_object->stubs());
    }

    public function testStubCallsStubsWithInvoke()
    {
        $this->_object->stubs();
        $actual = $this->_stubs->moka()->report('stubs')[0];
        $this->assertEquals(['invoke'], $actual);
    }

    public function testStubReturnsReportResult()
    {
        $this->assertEquals('REPORT', $this->_object->report());
    }

    public function testReportCallsReportWithInvoke()
    {
        $this->_object->report();
        $report = $this->_stubs->moka()->report('report');
        $this->assertEquals(['invoke'], $report[0]);
    }

    public function testStubsReturnsStubsResult()
    {
        $this->assertEquals('INVOKE', ($this->_object)());
    }

    public function testInvokeCallsInvokeWithArgs()
    {
        ($this->_object)('ARG');
        $actual = $this->_stubs->moka()->report('invoke')[0];
        $this->assertEquals(['invoke', ['ARG']], $actual);
    }

}
