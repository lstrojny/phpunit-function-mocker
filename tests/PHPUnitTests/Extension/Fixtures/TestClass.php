<?php
namespace PHPUnitTests\Extension\Fixtures;

class TestClass
{
    public static function invokeGlobalFunction()
    {
        return strpos('ffoo', 'o');
    }

    public static function getGlobalConstant()
    {
        return CNT;
    }
}
