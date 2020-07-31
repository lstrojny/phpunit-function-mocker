<?php
namespace PHPUnitTests\Extension\FunctionMocker;

use PHPUnit\Extension\FunctionMocker\CodeGenerator;
use PHPUnit\Framework\TestCase;

class CodeGeneratorTest extends TestCase
{
    public function testGenerateFunctionMock()
    {
        $code = CodeGenerator::generateFunction('Test\Namespace', 'strlen');

        $expected = <<<'EOS'
namespace Test\Namespace
{
    function strlen(...$args)
    {
        if (!isset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][__NAMESPACE__])) {
            return \strlen(...$args);
        }

        return $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][__NAMESPACE__]->strlen(...$args);
    }
}
EOS;
        self::assertSame($expected, $code);
    }

    public function testGenerateStringConstantMock()
    {
        $code = CodeGenerator::generateConstant('Test\Namespace', 'CONSTANT', 'value');

        $expected = <<<'EOS'
namespace Test\Namespace
{
    if (!defined(__NAMESPACE__ . '\\CONSTANT')) {
        define(__NAMESPACE__ . '\\CONSTANT', 'value');
    } elseif (CONSTANT !== 'value') {
        throw new \RuntimeException(sprintf('Cannot redeclare constant "CONSTANT" in namespace "%s". Already defined as "%s"', __NAMESPACE__, 'value'));
    }
}
EOS;
        self::assertSame($expected, $code);
    }

    public function testGenerateIntegerConstantMock(): void
    {
        $code = CodeGenerator::generateConstant('Test\Namespace', 'CONSTANT', 123);

        $expected = <<<'EOS'
namespace Test\Namespace
{
    if (!defined(__NAMESPACE__ . '\\CONSTANT')) {
        define(__NAMESPACE__ . '\\CONSTANT', 123);
    } elseif (CONSTANT !== 123) {
        throw new \RuntimeException(sprintf('Cannot redeclare constant "CONSTANT" in namespace "%s". Already defined as "%s"', __NAMESPACE__, 123));
    }
}
EOS;
        self::assertSame($expected, $code);
    }
}
