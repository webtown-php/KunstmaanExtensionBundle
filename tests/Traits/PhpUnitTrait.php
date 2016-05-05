<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2016.05.05.
 * Time: 11:55
 */

namespace Webtown\KunstmaanExtensionBundle\Tests\Traits;

trait PhpUnitTrait
{
    // @todo (Chris) Az alábbiakat inkább a PHPUnitTrait-ből betölteni, ha már külön bundle lett a WebtownTestingBundle
    protected function callObjectProtectedMethod($object, $methodName, $args = [])
    {
        $reflMethod = new \ReflectionMethod(get_class($object), $methodName);
        $reflMethod->setAccessible(true);

        return $reflMethod->invokeArgs($object, $args);
    }

    protected function setObjectProtectedAttribute($object, $attributeName, $value)
    {
        $property = new \ReflectionProperty(get_class($object), $attributeName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    protected function getObjectProtectedAttribute($object, $attributeName)
    {
        $property = new \ReflectionProperty(get_class($object), $attributeName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    protected function assertObjectProtectedAttribute($object, $attributeName, $value)
    {
        $current = $this->getObjectProtectedAttribute($object, $attributeName);
        $this->assertEquals($current, $value);
    }

    protected function assertEntityProperties($entity, $properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->assertObjectProtectedAttribute($entity, $key, $value);
        }
    }
}
