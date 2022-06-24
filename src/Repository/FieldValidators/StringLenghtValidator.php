<?php
declare(strict_types=1);

namespace xTest\Repository\FieldValidators;

use xTest\Entity\Entity;

class StringLenghtValidator implements Validator
{
    public int $max;
    public int $min;
    public int $is;
    public bool $allowEmpty=true;
    public ?string $encoding = null;

    private const DEFAULT_ENCODING = 'UTF-8';

    public function validateAttribute(Entity $object, string $attribute): bool
    {
        $value = $object->$attribute ?? '';
        $isValid = true;
        if (!$this->allowEmpty && !isset($value)) {
            return false;
        } elseif ($this->allowEmpty && !isset($value)) {
            return true;
        }

        if (is_array($value)) {
            $isValid = false;
        }
        if (function_exists('mb_strlen')) {
            $length = mb_strlen($value, $this->encoding ?? self::DEFAULT_ENCODING);
        } else {
            $length = strlen($value);
        }

        if (isset($this->min) && $length < $this->min) {
            $isValid = false;
        }
        if(isset($this->max) && $length > $this->max) {
            $isValid = false;
        }
        if (isset($this->is) && $length !== $this->is) {
            $isValid = false;
        }
        return $isValid;
    }
}