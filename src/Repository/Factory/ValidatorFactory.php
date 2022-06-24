<?php
declare(strict_types=1);

namespace xTest\Repository\Factory;

use xTest\Repository\FieldValidators\Validator;
use xTest\Repository\RepositoryException;

class ValidatorFactory
{
    public static function createInstance(string $class, $config): Validator
    {
        if (!class_exists($class)) {
            throw new RepositoryException("Couldn't load Validator: " . $class);
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