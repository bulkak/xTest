<?php
declare(strict_types=1);

namespace xTest\Entity\FieldValidators;

use xTest\Entity\Entity;

final class RegExpFieldValidator implements FieldValidator
{
    public string $pattern;
    public bool $allowEmpty = true;
    public bool $not = false;

    public function validateAttribute(Entity $object, string $attribute): bool
    {
        $isValid = true;
        $value = $object->$attribute ?? '';
        if (!$this->allowEmpty && !isset($value)) {
            return false;
        } elseif ($this->allowEmpty && !isset($value)) {
            return true;
        }

        if (!isset($this->pattern)) {
            throw new ValidatorException('The "pattern" property must be specified 
                with a valid regular expression.');
        }
        if (is_array($value) ||
            (!$this->not && !preg_match($this->pattern, $value)) ||
            ($this->not && preg_match($this->pattern, $value)))
        {
            $isValid = false;
        }

        return $isValid;
    }
}


