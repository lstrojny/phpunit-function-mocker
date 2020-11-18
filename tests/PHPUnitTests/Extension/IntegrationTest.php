<?php
namespace PHPUnitTests\Extension;

use PHPUnit\Extension\FunctionMocker;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/Fixtures/TestClass.php';

class IntegrationTest extends TestCase
{
    private $php;

    protected function setUp(): void
    {
        $this->php = FunctionMocker::start($this, 'PHPUnitTests\Extension\Fixtures')
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
        FunctionMocker::tearDown();
        $this->assertSame(2, \PHPUnitTests\Extension\Fixtures\TestClass::invokeGlobalFunction());
    }
}
