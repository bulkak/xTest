<?php
declare(strict_types=1);

namespace xTest\Repository\FieldValidators;

use xTest\Entity\Entity;

class BlackListContainsStringValidator implements Validator
{
    public string $blackListSource;

    public function validateAttribute(Entity $object, string $attribute): bool
    {
        $isValid = true;
        if (isset($this->blackListSource)) {
            $filePath = dirname(__FILE__) . '/../../../resources/blacklists/' . $this->blackListSource;
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