<?php

namespace shagabutdinov\moka\readme;

class UsersController
{
    private $_userClass;

    public function __construct($userClass = 'User')
    {
        $this->_userClass = $userClass;
    }

    public function find($id)
    {
        return json_encode($this->_userClass::find($id));
    }
}

class UsersControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testMainReturnsUser()
    {
        $userClass = \shagabutdinov\Moka::stubClass(null, ['::find' => 'USER']);
        $controller = new UsersController($userClass);
        $this->assertEquals('"USER"', $controller->find(1000));
    }

    public function testMainCallsFind()
    {
        $userClass = \shagabutdinov\Moka::stubClass(null, ['::find' => 'USER']);
        $controller = new UsersController($userClass);
        $controller->find(1000);
        // check that `find` was called with 100
        $this->assertEquals(
            [1000],
            $userClass::$moka->report('find')[0]
        );
    }
}
