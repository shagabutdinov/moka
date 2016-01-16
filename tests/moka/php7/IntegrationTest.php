<?php

namespace shagabutdinov\moka\php7;

class MokaTest extends \PHPUnit_Framework_TestCase
{
    const HELPER = '\shagabutdinov\moka\php7\MokaTestHelper';

    public function testStubHasParent()
    {
        $class = \shagabutdinov\Moka::stubClass(self::HELPER, []);
        $this->assertInstanceOf(self::HELPER, new $class());
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

    public function methodWithString(string $arg)
    {
    }

    public function methodWithInteger(int $arg)
    {
    }

    public function methodWithFloat(float $arg)
    {
    }

    public function methodWithBool(bool $arg)
    {
    }

    public function methodWithClass(MokaTestHelper $arg)
    {
    }
}
