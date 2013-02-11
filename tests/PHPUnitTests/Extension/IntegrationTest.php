<?php
require_once __DIR__ . '/Fixtures/TestClass.php';

class PHPUnitTests_Extension_IntegrationTest extends PHPUnit_Framework_TestCase
{
    private $php;

    public function setUp()
    {
        $this->php = PHPUnit_Extension_FunctionMocker::start($this, 'PHPUnitTests\Extension\Fixtures')
            ->mockFunction('strpos')
            ->getMock();
    }

    public function testMocked()
    {
        $this->php
            ->expects($this->once())
            ->method('strpos')
            ->with('ffoo', 'o')
            ->will($this->returnValue('mocked'));

        $this->assertSame('mocked', \PHPUnitTests\Extension\Fixtures\TestClass::invokeGlobalFunction());
    }

    public function testMockingGlobalFunctionAndCallingOriginalAgain()
    {
        $this->testMocked();
        PHPUnit_Extension_FunctionMocker::tearDown();
        $this->assertSame(2, \PHPUnitTests\Extension\Fixtures\TestClass::invokeGlobalFunction());
    }
}
