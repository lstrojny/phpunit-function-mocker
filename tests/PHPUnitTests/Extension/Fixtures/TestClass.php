<?php
namespace PHPUnitTests\Extension\Fixtures;

class TestClass
{
    public function invokeGlobalFunction()
    {
        return strpos('ffoo', 'o');
    }
}
