Moka: php moking library
========================

The php mocking library.

Status
------

![build status](https://travis-ci.org/shagabutdinov/moka.svg?branch=master)


Features
--------

  * Create stubs and mocks with or without parent
  * Create stubs and mocks for classes
  * Stub static and instance methods
  * Inspect stubs or mocks call history
  * Inspect stub constructor arguments


Installation
------------

`composer require --dev shagabutdinov/moka`

Usage
-----

Create stubs:

```
$stub = \shagabutdinov\Moka::stub(null, [
    'method1',
    'method2' => 'DEFAULT',
    'method3' => 'DEFAULT'
]);

$classStub = \shagabutdinov\Moka::stubClass(null, [
    '::method1',
    '::method2' => 'DEFAULT',
    'method3' => 'DEFAULT'
]);

$mock = \shagabutdinov\Moka::mock('MyClass', ['ARG'], ['method' => 'DEFAULT']); // new MyClass('ARG')
$classMock = \shagabutdinov\Moka::mockClass('MyClass', ['::method' => 'DEFAULT']);
```

Attach stubs extra behaviour:

```
$stub->moka()->stubs('method2')->with('ARG1', 'ARG2')->returns('RESULT');
$stub->moka()->stubs('method3')->returns('RESULT');
$mock->moka()->stubs('method')->on(function() {
  // some condition
})->returns('RESULT');

$classStub::$moka->stubs('method1')->returns('RESULT');
$classStub::$moka->stubs('method2')->with('ARG1')->returns('RESULT_1');
$classStub::$moka->stubs('method2')->at(1)->returns('RESULT_2');
```

Call stubs:

```
$stub->method1(); // error: method1 is not stubbed
$stub->method2(); // DEFAULT
$stub->method2('ARG1', 'ARG2'); // RESULT
$stub->method3(); // DEFAULT

$classStub::method1('ARG1'); // RESULT
$classStub::method2('ARG2'); // RESULT_1
$classStub::method2(); // RESULT_2
$classStub::method2(); // DEFAULT
```

Get stub report:

```
$stub->moka()->report('method2'); // [[], ['ARG1', 'ARG2']]
$classStub::moka->report('method1'); // [['ARG1'], ['ARG2']]
```


Examples
--------

Create object with method `METHOD` that returns `RESULT`:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'RESULT']);
$this->assertEquals('RESULT', $stub->method());
```

Redefine result on mock:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'OLD']);
$stub->moka()->stubs('method')->returns('NEW');
$this->assertEquals('NEW', $stub->method());
```


Set specific return value for argument:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'DEFAULT']);
$stub->moka()->stubs('method')->with('ARG')->returns('RESULT');
$this->assertEquals('RESULT', $stub->method('ARG'));
```

Set specific return value for callback:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'DEFAULT']);
$stub->moka()->stubs('method')->on(function() { return true; })->
    returns('RESULT');
$this->assertEquals('RESULT', $stub->method('ARG'));
```

Set specific return value for second call:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'DEFAULT']);
$stub->moka()->stubs('method')->at(1)->returns('RESULT');
$stub->method(); // DEFAULT
$this->assertEquals('RESULT', $stub->method());
```

Set callback for stub:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'DEFAULT']);
$stub->moka()->stubs('method')->calls(function() { return 'RESULT'; });
$this->assertEquals('RESULT', $stub->method('ARG'));
```

Check method call count:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'RESULT']);
$stub->method();
$this->assertEquals(1, sizeof($stub->moka()->report('method')));
```

Check method arguments:

```
$stub = \shagabutdinov\Moka::stub(null, ['method' => 'RESULT']);
$stub->method('ARG');
$this->assertEquals(['ARG'], $stub->moka()->report('method')[0]);
```

Stub static method:

```
$class = \shagabutdinov\Moka::stubClass(null, ['::method' => 'RESULT']);
$this->assertEquals('RESULT', $class::method());
```

Apply arguments checking on class:

```
$class = \shagabutdinov\Moka::stubClass(null, ['::method' => 'DEFAULT']);
$class::$moka->stubs('method')->with('ARG')->returns('RESULT');
$this->assertEquals('RESULT', $class::method('ARG'));
```

Stub only some methods of class and leave another unchanged:

```
$class = \shagabutdinov\Moka::mockClass('\shagabutdinov\Moka', ['::method' => 'RESULT']);
$this->assertEquals('RESULT', $class::method());
$this->assertInstanceOf('\shagabutdinov\moka\Spy', $class::spy());
```

Report static calls arguments:

```
$class = \shagabutdinov\Moka::stubClass(null, ['::method' => 'RESULT']);
$class::method('ARG');
$this->assertEquals(['ARG'], $class::$moka->report('method')[0]);
```

Report class instance arguments:

```
$class = \shagabutdinov\Moka::stubClass(null, ['call' => 'RESULT']);
(new $class())->call('ARG');
$instance = $class::$moka->instance(0);
$this->assertEquals(['ARG'], $instance->moka()->report('call')[0]);
```

Report constructor argument:

```
$class = \shagabutdinov\Moka::stubClass(null, ['__construct' => null]);
new $class('ARG1', 'ARG2');
$report = $class::$moka->instance(0)->moka()->report('__construct');
$this->assertEquals(['ARG1', 'ARG2'], $report[0]);
```

Create stub with parent:

```
$stub = \shagabutdinov\Moka::stub('\Exception', ['getName' => 'NAME']);
$this->assertInstanceOf('\Exception', $stub);
```

Create test spy (similar as in sinon js, but with less methods):

```
$spy = \shagabutdinov\Moka::spy('RESULT');
$this->assertEquals('RESULT', $spy());
```

Adjust spy behaviour:

```
$spy = \shagabutdinov\Moka::spy('DEFAULT');
$spy->stubs()->with('ARG')->returns('RESULT');
$this->assertEquals('RESULT', $spy('ARG'));
```

Use cases
---------


### Testing objects that calls static methods

It is difficult to test classes that call static methods of other classes.
For example there is `UsersController` that calls `User::find`:

```
class UsersController
{

    public function main()
    {
        // There are rumors that static methods are forbidden in some php teams...
        return json_encode(User::find(1));
    }
}

```

Here is how can you test it using `moka`:

```
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
        $this->assertEquals([1000], $userClass::$moka->report('find')[0]);
    }
}
```

Note that there is no need to stub `json_encode` as its considered to be stable,
covered with tests on php side, isolated from envirenoment and fast enough to
not slow down rest of tests.

Syntax `$this->_userClass::find($id)` is available only for `php 7`; in earlier
version you should do: `call_user_func_array([$this->_userClass, 'find'], [$id])`


### Testing objects that calls functions

It is difficult to test classes that call functions. For example there is
`UserData` that calls `file_get_contents`:

```
class UserData
{
    const DATA_PATH = '/data';

    public function get($id)
    {
        $path = self::DATA_PATH . '/' . $id . '.json';
        return json_decode(file_get_contents($path));
    }
}
```

Here is how can you test it using `moka`:

```
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
```


### Objects with a lot of dependencies

It is not recommended to create objects with a lot of dependecies, but therefore
everybody does it (almost each framework and developer). There is a way to do
that by grouping dependecies in array:

```
class MyClass
{
    private $_userClass;
    private $_roleClass;
    private $_otherClass;

    public function __construct($arg1, $arg2, $dependecies = [
        'user_class' => 'User',
        'role_class' => 'Role',
        'other_class' => 'Other',
    ])
    {
        $this->_userClass = $dependecies['user_class'];
        $this->_roleClass = $dependecies['role_class'];
        $this->_otherClass = $dependecies['other_class'];

        // ...
    }
}
```


### Testing static methods

We do not create classes with static methods that calls external dependencies
(but decorators allowed and we do not test it) in `shagabutdinov`, but only use such
classes. So `moka` does not support such kind of testing:


```
class MyClassWithStaticMethods
{

    public static function call()
    {
        OtherClass::call(); // unmokable with moka
    }

}
```

You can contact us in order to add support for such testing.


Faq
---

> How does it work?

It creates php code for each `mock`, `stub`, `mockClass` or `stubClass` call
and evaluates it through php `eval` function.

> Which version of php are required?

It should works with `php >= 5.6`, but but if you use [type hinting](http://php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration)
`php 7` is required. It also should work with `php >= 5.4` but we don't test it
as phpunit does not support this version of php.

> Can I have report for methods without stubbing/mocking it?
>
> ```
> $class = \shagabutdinov\Moka::mockClass('\shagabutdinov\Moka', []);
> $class::spy('ARG');
> $class::$moka->report('spy'); // []
> ```

No, you can not; you have to always stub or mock instance or class in order to
see it arguments. Note that observing for function without replacing it
behaviour is considered as bad practice in shagabutdinov, so moka does not allow
to do this.

Correct example:

```
$class = \shagabutdinov\Moka::mockClass('\shagabutdinov\Moka', ['spy' => 'MySpy']);
$class::spy('ARG');
$class::$moka->report('spy'); // [['ARG']]
```

> Is it fast and can run thouthands of unit tests in milliseconds?

I don't think so. Also `moka` is memory consumptive because it stores history
of all stubbed methods calls in static classes. This will change in future.

> Is it stable and suitable for production use?

No. We need some time to test it carefully and fix last bugs in order to make
`moka` stable. You can help us to do that by trying `moka` and opening issues.

> How can I contribute?

Open a pull request:

* don't forget to put yourself in authors
* all tests should pass
* phpcs should not display any warnings
* pull request branch should contains only one commit
* pull request branch should starts from master


Similar projects
----------------

* [PHPUnit test doubles](https://phpunit.de/manual/current/en/test-doubles.html)
* [Mockery](https://github.com/padraic/mockery)
* [Phake](http://phake.readthedocs.org/en/2.1/)


License
----------------

The MIT License (MIT)


Authors
-------

[Leonid Shagabutdinov](http://github.com/shagabutdinov)