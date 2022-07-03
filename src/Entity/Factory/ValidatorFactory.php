<?php
declare(strict_types=1);

namespace xTest\Entity\Factory;

use xTest\Entity\EntityMetaException;
use xTest\Entity\FieldValidators\FieldValidator;

class ValidatorFactory
{
    public static function createInstance(string $class, $config): FieldValidator
    {
        if (!class_exists($class)) {
            throw new EntityMetaException("Couldn't load Validator: " . $class);
        }
        $instance = new $class();
        foreach ($config as $key => $value) {
            if (property_exists($instance, $key)) {
                $instance->$key = $value;
            }
        }
        return $instance;
    }
}