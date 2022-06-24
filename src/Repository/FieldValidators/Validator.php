<?php

namespace xTest\Repository\FieldValidators;

use xTest\Entity\Entity;

interface Validator
{
    /**
     * Validates the attribute of the object.
     */
    public function validateAttribute(Entity $object, string $attribute): bool;
}