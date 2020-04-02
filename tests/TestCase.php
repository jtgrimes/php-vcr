<?php

namespace Tests;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public function callProtected($object, $methodName, $args = [])
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return  $method->invokeArgs($object, $args);
    }

    public function setProtectedProperty($object, $propertyName, $value)
    {
        $class = new \ReflectionClass($object);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    public function assertProtectedPropertyEquals($object, $propertyName, $expected, $message = null)
    {
        $class = new \ReflectionClass($object);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);
        $this->assertEquals($expected, $object->$propertyName, $message);
    }
}
