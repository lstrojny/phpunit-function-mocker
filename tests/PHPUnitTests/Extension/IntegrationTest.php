<?php
namespace PHPUnitTests\Extension;

use PHPUnit\Extension\FunctionMocker;
use PHPUnit\Framework\TestCase;
use PHPUnitTests\Extension\Fixtures\TestClass;

require_once __DIR__ . '/Fixtures/TestClass.php';

class IntegrationTest extends TestCase
{
    private $php;

    public function setUp()
    {
        $this->php = FunctionMocker::start($this, 'PHPUnitTests\Extension\Fixtures')
            ->mockFunction('strpos')
            ->mockConstant('CNT', 'val')
            ->getMock();
    }

    public function testMockFunction()
    {
        $this->php
            ->expects(self::once())
            ->method('strpos')
            ->with('ffoo', 'o')
            ->will(self::returnValue('mocked'));

        self::assertSame('mocked', TestClass::invokeGlobalFunction());
    }

    public function testMockingGlobalFunctionAndCallingOriginalAgain()
    {
        $this->testMockFunction();
        FunctionMocker::tearDown();
        self::assertSame(2, TestClass::invokeGlobalFunction());
    }

    public function testMockConstant()
    {
        self::assertSame('val', TestClass::getGlobalConstant());
    }
}
