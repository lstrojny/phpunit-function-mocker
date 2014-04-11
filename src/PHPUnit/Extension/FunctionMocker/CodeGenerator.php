<?php
class PHPUnit_Extension_FunctionMocker_CodeGenerator
{
    public static function generateCode($functionName, $namespaceName)
    {
        $template = <<<'EOS'
namespace %1$s
{
    function %2$s()
    {
        if (!isset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']['%1$s'])) {
            return call_user_func_array('%2$s', func_get_args());
        }

        return call_user_func_array(
            array($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']['%1$s'], '%2$s'),
            func_get_args()
        );
    }
}
EOS;

        return sprintf($template, $namespaceName, $functionName);
    }

    public static function defineFunction($functionName, $namespaceName)
    {
        $code = static::generateCode($functionName, $namespaceName);
        eval($code);
    }
}
