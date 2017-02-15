<?php
namespace PHPUnitTests\Extension\FunctionMocker;

use PHPUnit\Extension\FunctionMocker\CodeGenerator;
use PHPUnit\Framework\TestCase;

class CodeGeneratorTest extends TestCase
{
    public function testRetrieveSimpleFunctionMock()
    {
        $code = CodeGenerator::generateCode('strlen', 'Test\Namespace');

        $expected = <<<'EOS'
namespace Test\Namespace
{
    function strlen()
    {
        if (!isset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']['Test\Namespace'])) {
            return call_user_func_array('strlen', func_get_args());
        }

        return call_user_func_array(
            array($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']['Test\Namespace'], 'strlen'),
            func_get_args()
        );
    }
}
EOS;
        $this->assertEquals($expected, $code);
    }
}
