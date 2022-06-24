<?php
declare(strict_types=1);

namespace xTest\Repository\FieldValidators;

use xTest\Entity\Entity;

class MoreThanValidator implements Validator
{
    public string $compareWith;
    public bool $allowEmpty = true;

    public function validateAttribute(Entity $object, string $attribute): bool
    {
        $value = $object->$attribute ?? null;
        if (!$this->allowEmpty && !isset($value)) {
            return false;
        } elseif ($this->allowEmpty && !isset($value)) {
            return true;
        }
        $compareValue = $object->{$this->compareWith} ?? null;
        return $value > $compareValue;
    }
}