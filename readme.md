Moka: php moking library
========================

The php mocking library.

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

Create object with method 'METHOD' that returns 'RESULT':

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

Faq
---

  > How does it work?

  It creates php code for each mock, stub, mockClass or stubClass call and
  evaluates it through php `eval` function.

  > Can I have report for methods without stubbing/mocking it?
  >
  > ```
  > $class = \shagabutdinov\Moka::mockClass('\shagabutdinov\Moka', []);
  > $class::spy('ARG');
  > $class::$moka->report('spy'); // []
  > ```

  No, you can not; you have to always stub or mock instance or class in order to
  see it arguments. Note that observing for function without replacing it
  behaviour is considered as bad practice in shagabutdinov, so moka does not allow to
  do this.

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

  Open a pull request; don't forget to put yourself in authors. All tests should
  pass. Phpcs should not display any warnings.


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