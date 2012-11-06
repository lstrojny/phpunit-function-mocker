# PHPUnit function mocker extension

Allows mocking otherwise untestable PHP functions through the use of namespaces.

[![Build Status](https://secure.travis-ci.org/lstrojny/phpunit-function-mocker.png)](http://travis-ci.org/lstrojny/phpunit-function-mocker)

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
