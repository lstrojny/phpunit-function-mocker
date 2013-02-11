<?php
namespace PHPUnitTests\Extension\Fixtures;

class TestClass
{
    public static function invokeGlobalFunction()
    {
        return strpos('ffoo', 'o');
    }
}
