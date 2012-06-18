<?php

require_once __DIR__ . '/FunctionMocker/CodeGenerator.php';

class PHPUnit_Extension_FunctionMocker
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $testCase;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $functions = array();

    /**
     * @var array
     */
    private static $mockedFunctions = array();

    private function __construct(PHPUnit_Framework_TestCase $testCase, $namespace)
    {
        $this->testCase = $testCase;
        $this->namespace = trim($namespace, '\\');
    }

    public static function start(PHPUnit_Framework_TestCase $testCase, $namespace)
    {
        return new static($testCase, $namespace);
    }

    public function resetInstance()
    {
        unset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']);
    }

    public function mockFunction($function)
    {
        $function = trim(strtolower($function));

        if (!in_array($function, $this->functions, true)) {
            $this->functions[] = $function;
        }

        return $this;
    }

    public function getMock()
    {
        $mock = $this->testCase->getMock(
            'stdClass',
            $this->functions,
            array(),
            'PHPUnit_Extension_FunctionMocker_' . uniqid()
        );

        foreach ($this->functions as $function) {

            if (in_array($function, static::$mockedFunctions, true)) {
                continue;
            }

            $code = PHPUnit_Extension_FunctionMocker_CodeGenerator::generateCode($function, $this->namespace);
            eval($code);

            static::$mockedFunctions[] = $function;
        }

        if (!isset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'])) {
            $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'] = array();
        }

        $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][$this->namespace] = $mock;

        return $mock;
    }
}
