<?php

namespace shagabutdinov;

class MokaTest extends \PHPUnit_Framework_TestCase
{
    const HELPER = 'shagabutdinov\MokaTestHelper';

    public function setUp()
    {
        $this->_helper = new MokaTestStub();
    }

    private function _stubClass($parent, $methods = [])
    {
        $class = Moka::stubClass($parent, $methods);
        $class::$moka = $this->_helper;
        return $class;
    }

    private function _mockClass($parent, $methods = [])
    {
        $class = Moka::mockClass($parent, $methods);
        $class::$moka = $this->_helper;
        return $class;
    }

    private function _stub($parent, $methods = [])
    {
        $class = $this->_stubClass($parent, $methods);
        $stub = new $class();
        $stub->__moka = $this->_helper;
        return $stub ;
    }

    private function _mock($parent, $methods = [])
    {
        $class = $this->_mockClass($parent, $methods);
        $stub = new $class();
        $stub->__moka = $this->_helper;
        return $stub ;
    }

    public function testCreateCreatesClass()
    {
        $class = $this->_stubClass(null, []);
        $this->assertTrue(class_exists($class));
    }

    public function testStubHasParent()
    {
        $stub = $this->_stub(self::HELPER, []);
        $this->assertInstanceOf(self::HELPER, $stub);
    }

    public function testStubRetunsMoka()
    {
        $stub = $this->_stub(self::HELPER);
        $this->assertInstanceOf('\shagabutdinov\MokaTestStub', $stub->moka());
    }

    public function testClassRetunsMoka()
    {
        $class = $this->_stubClass(self::HELPER, ['call']);
        $this->assertInstanceOf('\shagabutdinov\MokaTestStub', $class::$moka);
    }

    // stub instance methods

    public function testStubInstanceHasMethod()
    {
        $stub = $this->_stub(null, ['method']);
        $stub->method('ARG');
        $this->assertEquals(['method', ['ARG']], $this->_helper->invoke);
    }

    public function testStubInstanceHasParentStubMethod()
    {
        $stub = $this->_stub(self::HELPER, []);
        $stub->call('ARG');
        $this->assertEquals(['call', ['ARG']], $this->_helper->invoke);
    }

    public function testStubInstanceHasParentStubMethodResult()
    {
        $stub = $this->_stub(self::HELPER, []);
        $this->assertEquals('INVOKE', $stub->call('ARG'));
    }

    public function testStubInstanceHasParentStubMethodWithDefinition()
    {
        $stub = $this->_stub(self::HELPER, ['call']);
        $stub->call('ARG');
        $this->assertEquals(['call', ['ARG']], $this->_helper->invoke);
    }

    public function testStubInstanceHasParentStubMethodWithDefinitionResult()
    {
        $stub = $this->_stub(self::HELPER, ['call']);
        $this->assertEquals('INVOKE', $stub->call('ARG'));
    }

    // stub class methods

    public function testStubClassHasMethod()
    {
        $class = $this->_stubClass(null, ['::method']);
        $class::method('ARG');
        $this->assertEquals(['method', ['ARG']], $this->_helper->invoke);
    }

    public function testStubClassHasParentStubMethod()
    {
        $class = $this->_stubClass(self::HELPER, []);
        $class::callStatic('ARG');
        $this->assertEquals(['callStatic', ['ARG']], $this->_helper->invoke);
    }

    public function testStubClassHasParentStubMethodResult()
    {
        $class = $this->_stubClass(self::HELPER, []);
        $this->assertEquals('INVOKE', $class::callStatic('ARG'));
    }

    public function testStubClassHasParentStubMethodWithDefinition()
    {
        $class = $this->_stubClass(self::HELPER, ['::callStatic']);
        $class::callStatic('ARG');
        $this->assertEquals(['callStatic', ['ARG']], $this->_helper->invoke);
    }

    public function testStubClassHasParentStubMethodWithDefinitionResult()
    {
        $class = $this->_stubClass(self::HELPER, ['::callStatic']);
        $this->assertEquals('INVOKE', $class::callStatic('ARG'));
    }

    // mock instance methods

    public function testMockInstanceHasArgs()
    {
        $mock = Moka::mock(self::HELPER, ['ARGS'], ['method']);
        $this->assertEquals(['ARGS'], $mock->args);
    }

    public function testMockInstanceHasMethod()
    {
        $mock = $this->_mock(null, ['method']);
        $mock->method('ARG');
        $this->assertEquals(['method', ['ARG']], $this->_helper->invoke);
    }

    public function testMockInstanceHasNoParentStubMethod()
    {
        $mock = $this->_mock(self::HELPER, []);
        $mock->call('ARG');
        $this->assertEquals(null, $this->_helper->invoke);
    }

    public function testMockInstanceHasParentStubMethodResult()
    {
        $mock = $this->_mock(self::HELPER, []);
        $this->assertEquals('CALL', $mock->call('ARG'));
    }

    public function testMockInstanceHasParentStubMethodWithDefinition()
    {
        $mock = $this->_mock(self::HELPER, ['call']);
        $mock->call('ARG');
        $this->assertEquals(['call', ['ARG']], $this->_helper->invoke);
    }

    public function testMockInstanceHasParentStubMethodWithDefinitionResult()
    {
        $mock = $this->_mock(self::HELPER, ['call']);
        $this->assertEquals('INVOKE', $mock->call('ARG'));
    }

    // mock class methods

    public function testMockClassHasMethod()
    {
        $class = $this->_mockClass(null, ['::method']);
        $class::method('ARG');
        $this->assertEquals(['method', ['ARG']], $this->_helper->invoke);
    }

    public function testMockClassHasNotParentStubMethod()
    {
        $class = $this->_mockClass(self::HELPER, []);
        $class::callStatic('ARG');
        $this->assertEquals(null, $this->_helper->invoke);
    }

    public function testMockClassHasParentStubMethodResult()
    {
        $class = $this->_mockClass(self::HELPER, []);
        $this->assertEquals('CALL_STATIC', $class::callStatic('ARG'));
    }

    public function testMockClassHasParentStubMethodWithDefinition()
    {
        $class = $this->_mockClass(self::HELPER, ['::callStatic']);
        $class::callStatic('ARG');
        $this->assertEquals(['callStatic', ['ARG']], $this->_helper->invoke);
    }

    public function testMockClassHasParentStubMethodWithDefinitionResult()
    {
        $class = $this->_mockClass(self::HELPER, ['::callStatic']);
        $this->assertEquals('INVOKE', $class::callStatic('ARG'));
    }

    // mock methods with mull

    public function testMockWithNull()
    {
        $mock = $this->_mock(self::HELPER, ['methodWithNull' => true]);
        $this->assertEquals('INVOKE', $mock->methodWithNull());
    }
}

class MokaTestHelper
{
    public $args;

    public function __construct()
    {
        $this->args = func_get_args();
    }

    public function call()
    {
        return 'CALL';
    }

    public static function callStatic()
    {
        return 'CALL_STATIC';
    }

    public function methodWithDefault($result = 'RESULT')
    {
    }

    public function methodWithNull($result = null)
    {
    }

    public function methodWithArray(array $result)
    {
    }

    public function methodWithCallable(callable $result)
    {
    }

    public function methodWithReference(&$result)
    {
    }

    final public function methodWithFinal(array $result)
    {
    }
}

class MokaTestStub
{

    public $__addInstance = null;
    public $invoke = null;
    public $stubs = null;

    public function __addInstance($instance)
    {
        $this->__addInstance = $instance;
    }

    public function stubs($method)
    {
        $this->stubs = [$method, $args];
        return 'STUBS';
    }

    public function stub($method)
    {
        return 'STUB';
    }

    public function invoke($method, $args)
    {
        $this->invoke = [$method, $args];
        return 'INVOKE';
    }
}
