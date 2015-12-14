<?php
/**
 * @copyright Copyright (c) 2015 Orba Sp. z o.o. (http://orba.pl)
 */

namespace Orba\Payupl\Test;

class Util
{
    /**
     * Calls private or protected method of specified object.
     *
     * @param object $obj
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public static function callMethod($obj, $name, array $args = [])
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    /**
     * Sets private or protected property of specified object.
     *
     * @param object $obj
     * @param string $name
     * @param mixed $value
     */
    public static function setProperty($obj, $name, $value)
    {
        $class = new \ReflectionClass($obj);
        $property = $class->getProperty($name);
        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }
}
