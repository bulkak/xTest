<?php

namespace xTest\Entity\FieldValidators;

use xTest\Entity\Entity;

interface FieldValidator
{
    /**
     * Validates the attribute of the object.
     */
    public function validateAttribute(Entity $object, string $attribute): bool;
}