<?php
declare(strict_types=1);

namespace xTest\Entity\FieldValidators;

use xTest\Entity\Entity;

class BlackListContainsStringFieldValidator implements FieldValidator
{
    public string $blackListSource;

    public function validateAttribute(Entity $object, string $attribute): bool
    {
        $isValid = true;
        if (isset($this->blackListSource)) {
            $filePath = dirname(__FILE__) . '/../../../resources/blacklists/' . $this->blackListSource . '.php';
            if (file_exists($filePath)) {
                $blackList = require($filePath);
                if (is_array($blackList)) {
                    $value = $object->$attribute;
                    foreach ($blackList as $stopWord) {
                        if (str_contains($value, $stopWord)) {
                            $isValid = false;
                        }
                    }
                }
            }
        }
        return $isValid;
    }
}