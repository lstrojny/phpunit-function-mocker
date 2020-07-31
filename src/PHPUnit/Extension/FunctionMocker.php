<?php
namespace PHPUnit\Extension;

use PHPUnit\Extension\FunctionMocker\CodeGenerator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use function bin2hex;
use function random_bytes;

class FunctionMocker
{
    /** @var TestCase */
    private $testCase;

    /** @var string */
    private $namespace;

    /** @var array */
    private $functions = array();

    /** @var array */
    private $constants = [];

    /** @var array */
    private static $mockedFunctions = array();

    private function __construct(TestCase $testCase, $namespace)
    {
        $this->testCase = $testCase;
        $this->namespace = trim($namespace, '\\');
    }

    /**
     * Create a mock for the given namespace to override global namespace functions.
     *
     * Example: PHP global namespace function setcookie() needs to be overridden in order to test
     * if a cookie gets set. When setcookie() is called from inside a class in the namespace
     * \Foo\Bar the mock setcookie() created here will be used instead to the real function.
     */
    public static function start(TestCase $testCase, string $namespace): self
    {
        return new static($testCase, $namespace);
    }

    public static function tearDown(): void
    {
        unset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER']);
    }

    public function mockFunction(string $function): self
    {
        $function = trim(strtolower($function));

        if (!in_array($function, $this->functions, true)) {
            $this->functions[] = $function;
        }

        return $this;
    }

    public function mockConstant(string $constant, $value): self
    {
        $this->constants[trim($constant)] = $value;

        return $this;
    }

    public function getMock(): MockObject
    {
        $mock = $this->testCase->getMockBuilder('stdClass')
            ->setMethods($this->functions)
            ->setMockClassName('PHPUnit_Extension_FunctionMocker_' . bin2hex(random_bytes(16)))
            ->getMock();

        foreach ($this->constants as $constant => $value) {
            CodeGenerator::defineConstant($this->namespace, $constant, $value);
        }

        foreach ($this->functions as $function) {
            $fqFunction = $this->namespace . '\\' . $function;
            if (in_array($fqFunction, static::$mockedFunctions, true)) {
                continue;
            }

            CodeGenerator::defineFunction($this->namespace, $function);
            static::$mockedFunctions[] = $fqFunction;
        }

        if (!isset($GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'])) {
            $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'] = array();
        }

        $GLOBALS['__PHPUNIT_EXTENSION_FUNCTIONMOCKER'][$this->namespace] = $mock;

        return $mock;
    }
}
