<?php
class PHPUnitTests_Extension_FunctionMockerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->functionMocker = PHPUnit_Extension_FunctionMocker::start($this, 'My\TestNamespace');
    }

    public function tearDown()
    {
        PHPUnit_Extension_FunctionMocker::tearDown();
    }

    public function testBasicMockingFunction()
    {
        $this->assertMockFunctionNotDefined('My\TestNamespace\strlen');

        $this->functionMocker
            ->mockFunction('strlen')
            ->mockFunction('substr');

        $this->assertMockFunctionNotDefined('My\TestNamespace\strlen');
        $this->assertMockFunctionNotDefined('My\TestNamespace\substr');

        $mock = $this->functionMocker->getMock();

        $this->assertMockFunctionDefined('My\TestNamespace\strlen', 'My\TestNamespace');
        $this->assertMockFunctionDefined('My\TestNamespace\substr', 'My\TestNamespace');

        $mock
            ->expects($this->once())
            ->method('strlen')
            ->will($this->returnValue('mocked strlen()'))
        ;
        $mock
            ->expects($this->once())
            ->method('substr')
            ->will($this->returnCallback(
                function() {
                    return func_get_args();
                }
            ))
        ;

        $this->assertMockObjectPresent('My\TestNamespace', $mock);
        $this->assertSame('mocked strlen()', My\TestNamespace\strlen('foo'));
        $this->assertSame(array('foo', 0, 3), My\TestNamespace\substr('foo', 0, 3));
    }

    public function testNamespaceLeadingAndTrailingSlash()
    {
        $this->functionMocker = PHPUnit_Extension_FunctionMocker::start($this, '\My\TestNamespace\\');

        $this->assertMockFunctionNotDefined('My\TestNamespace\strpos');

        $this->functionMocker
            ->mockFunction('strpos');

        $this->assertMockFunctionNotDefined('My\TestNamespace\strpos');

        $mock = $this->functionMocker->getMock();

        $this->assertMockFunctionDefined('My\TestNamespace\strpos', 'My\TestNamespace');

        $mock
            ->expects($this->once())
            ->method('strpos')
            ->will($this->returnArgument(1))
        ;

        $this->assertMockObjectPresent('My\TestNamespace', $mock);
        $this->assertSame('b', My\TestNamespace\strpos('abc', 'b'));
    }

    public function testFunctionsAreUsedLowercase()
    {
        $this->assertMockFunctionNotDefined('My\TestNamespace\myfunc');

        $this->functionMocker
            ->mockFunction('myfunc')
            ->mockFunction(' myfunc   ')
            ->mockFunction('MYFUNC');

        $this->assertMockFunctionNotDefined('My\TestNamespace\myfunc');

        $mock = $this->functionMocker->getMock();

        $this->assertMockFunctionDefined('My\TestNamespace\myfunc', 'My\TestNamespace');

        $mock
            ->expects($this->once())
            ->method('myfunc')
            ->will($this->returnArgument(0))
        ;

        $this->assertMockObjectPresent('My\TestNamespace', $mock);
        $this->assertSame('abc', My\TestNamespace\myfunc('abc'));
    }

    public function testUseOneFunctionMockerMoreThanOnce()
    {
        $this->assertMockFunctionNotDefined('My\TestNamespace\strtr');

        $this->functionMocker
            ->mockFunction('strtr');

        $this->assertMockFunctionNotDefined('My\TestNamespace\strtr');

        $this->functionMocker->getMock();

        $this->functionMocker
            ->mockFunction('strtr');

        $mock = $this->functionMocker->getMock();

        $this->assertMockFunctionDefined('My\TestNamespace\strtr', 'My\TestNamespace');

        $mock
            ->expects($this->once())
            ->method('strtr')
            ->with('abcd')
            ->will($this->returnArgument(0))
        ;

        $this->assertMockObjectPresent('My\TestNamespace', $mock);

        try {
            $this->assertSame('abc', My\TestNamespace\strtr('abc'));
            $this->fail('Expected exception');
        } catch (Exception $e) {
            $this->assertContains('does not match expected value', $e->getMessage());
        }

        /** Reset mock objects */
        $reflected = new ReflectionClass('PHPUnit_Framework_TestCase');
        $mockObjects = $reflected->getProperty('mockObjects');
        $mockObjects->setAccessible(true);
        $mockObjects->setValue($this, array());
    }

    public function testMockSameFunctionIsDifferentNamespaces()
    {
        $this->assertMockFunctionNotDefined('My\TestNamespace\foofunc');
        $this->functionMocker
            ->mockFunction('foofunc');
        $this->assertMockFunctionNotDefined('My\TestNamespace\foofunc');
        $this->functionMocker->getMock();
        $this->assertMockFunctionDefined('My\TestNamespace\foofunc', 'My\TestNamespace');

        $this->functionMocker = PHPUnit_Extension_FunctionMocker::start($this, 'My\TestNamespace2');
        $this->assertFalse(function_exists('My\TestNamespace2\foofunc'));
        $this->functionMocker
            ->mockFunction('foofunc');
        $this->assertFalse(function_exists('My\TestNamespace2\foofunc'));
        $this->functionMocker->getMock();
        $this->assertMockFunctionDefined('My\TestNamespace2\foofunc', 'My\TestNamespace2');
    }

    public function assertMockFunctionNotDefined($function)
    {
        $this->assertFalse(
            function_exists($function),
            sprintf('Function "%s()" was expected to be undefined', $function)
        );
        $this->assertArrayNotHasKey('__PHPUNIT_EXTENSION_FUNCTIONMOCKER', $GLOBALS);
    }

    public function assertMockFunctionDefined($function, $namespace)
    {
        $this->assertTrue(function_exists($function));
        $this->assertArrayHasKey('__PHPUNIT_EXTENSION_FUNCTIONMOCKER', $GLOBALS);
        $this->assertArrayHasKey($namespace, $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']);
    }

    public function assertMockObjectPresent($namespace, $mock)
    {
        $this->assertArrayHasKey('__PHPUNIT_EXTENSION_FUNCTIONMOCKER', $GLOBALS);
        $this->assertArrayHasKey($namespace, $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']);
        $this->assertSame($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][$namespace], $mock);
    }
}
