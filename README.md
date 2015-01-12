# PHPUnit function mocker extension

Allows mocking otherwise untestable PHP functions through the use of namespaces.

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/lstrojny/phpunit-function-mocker?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge) [![Build Status](https://secure.travis-ci.org/lstrojny/phpunit-function-mocker.svg)](http://travis-ci.org/lstrojny/phpunit-function-mocker) [![Dependency Status](https://www.versioneye.com/user/projects/523ed7fc632bac1b0b00b278/badge.png)](https://www.versioneye.com/user/projects/523ed7fc632bac1b0b00b278) [![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/lstrojny/phpunit-function-mocker.svg)](http://isitmaintained.com/project/lstrojny/phpunit-function-mocker "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/lstrojny/phpunit-function-mocker.svg)](http://isitmaintained.com/project/lstrojny/phpunit-function-mocker "Percentage of issues still open")

```php
<?php
namespace MyNamespace;

class Tool
{
    public function isString($string)
    {
        return strlen($string) > 0 && ctype_alpha($string);
    }
}
```

```php
<?php

require_once 'PHPUnit/Extension/FunctionMocker.php';

class MyTestCase extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->php = PHPUnit_Extension_FunctionMocker::start($this, 'MyNamespace')
            ->mockFunction('strlen')
            ->mockFunction('ctype_alpha')
            ->getMock();
    }

    /** @runInSeparateProcess */
    public function testIsStringUsesStrlenAndCtypeAlpha()
    {
        $this->php
            ->expects($this->once())
            ->method('strlen')
            ->with('foo')
            ->will($this->returnValue(3))
        ;
        $this->php
            ->expects($this->once())
            ->method('ctype_alpha')
            ->with('foo')
            ->will($this->returnValue(false))
        ;

        $tool = new MyNamespace\Tool();
        $this->assertFalse($tool->isString('foo'));
    }
}
```
### NOTE
Use `@runInSeparateProcess` annotation to make sure that the mocking is reliably working in PHP >=5.4
