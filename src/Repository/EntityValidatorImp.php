<?php
declare(strict_types=1);

namespace xTest\Repository;

use xTest\Entity\Entity;
use xTest\Entity\EntityMeta;

class EntityValidatorImp implements EntityValidator
{
    private array $errors = [];

    public function __construct(private readonly EntityMeta $entityMetadata){}

    public function validate(Entity $entity): bool
    {
        $isValid = true;
        foreach ($this->entityMetadata->publicFields as $prop) {
            $propName = $prop->getName();
            if ($prop->isInitialized($entity)) {
                if (!$this->validateProperty($entity, $propName)) {
                    $isValid = false;
                    $this->errors[] = vsprintf(
                        'Property {%s} of entity {%s} is not valid!',
                        [
                            $propName,
                            $entity::class
                        ]
                    );
                }
            } elseif ($this->checkIsColumnRequired($propName)) {
                $isValid = false;
                $this->errors[] = vsprintf(
                    'Property {%s} of entity {%s} must be initialized!',
                    [
                        $propName,
                        $entity::class
                    ]
                );
            }
        }
        return $isValid;
    }

    private function validateProperty(Entity $entity, $propName): bool
    {
        $isValid = true;
        if (!$this->validateFieldValue($entity, $propName)) {
            $isValid = false;
        }
        return $isValid;
    }

    private function validateFieldValue(Entity $entity, string $propName): bool
    {
        $validators = $this->entityMetadata->validators[$propName] ?? [];
        foreach ($validators as $validator) {
            if (!$validator->validateAttribute($entity, $propName)) {
                return false;
            }
        }
        return true;
    }

    private function checkIsColumnRequired($propName): bool
    {
        return in_array($propName, $this->entityMetadata->requiredFields);
    }

    public function flushErrors(): array
    {
        $errors = $this->errors;
        $this->errors = [];
        return $errors;
    }
}