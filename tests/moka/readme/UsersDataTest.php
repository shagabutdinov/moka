<?php

namespace shagabutdinov\moka\readme;

class UserData
{
    const DATA_PATH = '/data';
    private $_fileGetContents;

    public function __construct($fileGetContents = 'file_get_contents')
    {
        $this->_fileGetContents = $fileGetContents;
    }

    public function get($id)
    {
        // note parenthesis around $this->_fileGetContents in order to function
        $path = self::DATA_PATH . '/' . $id . '.json';
        return json_decode(($this->_fileGetContents)($path));
    }
}

class UserDataTest extends \PHPUnit_Framework_TestCase
{
    public function testGetReturnsFileContents()
    {
        $fileGetContents = \shagabutdinov\Moka::spy('"USER"');
        $userData = new UserData($fileGetContents);
        $this->assertEquals('USER', $userData->get(1000));
    }

    public function testGetCallsFileGetContentsWithUserId()
    {
        $fileGetContents = \shagabutdinov\Moka::spy('"USER"');
        $userData = new UserData($fileGetContents);
        $userData->get(1000);
        $this->assertEquals(['/data/1000.json'], $fileGetContents->report()[0]);
    }
}
