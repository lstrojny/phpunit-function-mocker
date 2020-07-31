<?php
namespace PHPUnit\Extension\FunctionMocker;

use function sprintf;
use function strtr;
use function var_export;

class CodeGenerator
{
    public static function generateFunction(string $namespace, string $function): string
    {
        $template = <<<'EOS'
namespace {namespace}
{
    function {function}(...$args)
    {
        if (!isset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][__NAMESPACE__])) {
            return \{function}(...$args);
        }

        return $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][__NAMESPACE__]->{function}(...$args);
    }
}
EOS;

        return self::renderTemplate($template, ['namespace' => $namespace, 'function' => $function]);
    }

    public static function defineFunction(string $namespace, string $function): void
    {
        $code = static::generateFunction($namespace, $function);
        eval($code);
    }

    public static function generateConstant($namespace, $constant, $value)
    {
        $template = <<<'EOS'
namespace {namespace}
{
    if (!defined(__NAMESPACE__ . '\\{constant}')) {
        define(__NAMESPACE__ . '\\{constant}', {value});
    } elseif ({constant} !== {value}) {
        throw new \RuntimeException(sprintf('Cannot redeclare constant "{constant}" in namespace "%s". Already defined as "%s"', __NAMESPACE__, {value}));
    }
}
EOS;

        return self::renderTemplate(
            $template,
            [
                'namespace' => $namespace,
                'constant' => $constant,
                'value' => var_export($value, true),
            ]
        );
    }

    public static function defineConstant(string $namespace, string $name, string $value): void
    {
        eval(self::generateConstant($namespace, $name, $value));
    }

    private static function renderTemplate(string $template, array $parameters): string
    {
        return strtr(
            $template,
            array_combine(
                array_map(
                    function (string $key): string {
                        return '{' . $key . '}';
                    },
                    array_keys($parameters)
                ),
                array_values($parameters)
            )
        );
    }
}
