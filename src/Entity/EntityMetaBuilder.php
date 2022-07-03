<?php
declare(strict_types=1);

namespace xTest\Entity;

use ReflectionClass;
use ReflectionProperty;
use xTest\Entity\Attributes\Generators\GeneratorAttribute;
use xTest\Entity\Attributes\Hydrators\HydratorAttribute;
use xTest\Entity\Attributes\Identifier;
use xTest\Entity\Attributes\Required;
use xTest\Entity\Attributes\TableName;
use xTest\Entity\Attributes\Unique;
use xTest\Entity\Attributes\Validators\ValidatorAttribute;
use xTest\Entity\Factory\ValidatorFactory;

class EntityMetaBuilder
{
    /** @var ReflectionProperty[] */
    private array $publicFields;

    private ReflectionClass $reflectionClass;

    public readonly EntityMeta $meta;

    public function __construct(string $class,
                                private readonly array $validatorsMap,
                                private readonly  array $hydratorsMap,
                                private readonly array $generatorsMap,
    ) {
        try {
            $this->reflectionClass = new ReflectionClass($class);
        } catch (\Throwable $e) {
            throw new EntityMetaException('Bad entity class to make it\'s metadata',0, $e);
        }
        $this->publicFields = $this->getPublicFields();
        $this->meta = new EntityMeta(
            $this->publicFields,
            $this->getIdentifierField(),
            $this->getTableName(),
            $this->getValidators(),
            $this->getHydrators(),
            $this->getGenerators(),
            $this->getRequiredFields(),
            $this->getUniqueFields()
        );
    }

    private function getPublicFields(): array
    {
        return $this->reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
    }

    private function getIdentifierField(): string
    {
        foreach ($this->publicFields as $property) {
            if ($property->getAttributes(Identifier::class)) {
                return $property->getName();
            }
        }
        throw new EntityMetaException('entity class must have a Identifier attribute');
    }


    private function getTableName(): string
    {
        $nameAttributes = $this->reflectionClass->getAttributes(TableName::class);
        if ($nameAttributes) {
            /** @var TableName $nameAttribute */
            $nameAttribute = $nameAttributes[0]->newInstance();
            return $nameAttribute->tableName;
        }
        throw new EntityMetaException('entity class must have a TableName attribute');
    }

    private function getValidators(): array
    {
        $result = [];
        foreach ($this->publicFields as $property) {
            $validators = $property->getAttributes(ValidatorAttribute::class, \ReflectionAttribute::IS_INSTANCEOF);
            foreach ($validators as $validator) {
                $config = [];
                $validator = $validator->newInstance();
                foreach ($validator as $validatorProperty => $value) {
                    $config[$validatorProperty] = $value;
                }
                if (isset($this->validatorsMap[$validator::class])) {
                    $result[$property->getName()][] = ValidatorFactory::createInstance($this->validatorsMap[$validator::class], $config);
                } else {
                    throw new EntityMetaException(vsprintf(
                        'Validator attribute {%s} doesn\'t  have reference to any class!',
                        [
                            $validator::class,
                        ]
                    ));
                }

            }
        }
        return $result;
    }

    private function getHydrators(): array
    {
        return $this->getClassesFromMapOfAttribute($this->hydratorsMap, HydratorAttribute::class);
    }

    private function getGenerators(): array
    {
        return $this->getClassesFromMapOfAttribute($this->generatorsMap, GeneratorAttribute::class);
    }

    private function getRequiredFields(): array
    {
        return $this->getFieldsContainsAttribute(Required::class);
    }

    private function getUniqueFields(): array
    {
        return $this->getFieldsContainsAttribute(Unique::class);
    }

    private function getClassesFromMapOfAttribute(array $map, string $attributeClass): array
    {
        $result = [];
        foreach ($this->publicFields as $property) {
            $attributes = $property->getAttributes($attributeClass, \ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                $attribute = $attribute->newInstance();
                if (isset($map[$attribute::class])) {
                    $result[$property->getName()] = $map[$attribute::class];
                } else {
                    throw new EntityMetaException(vsprintf(
                        'Attribute {%s} doesn\'t  have reference to any class!',
                        [
                            $attribute::class,
                        ]
                    ));
                }
            }
        }
        return $result;
    }

    private function getFieldsContainsAttribute(string $attributeClass): array
    {
        $result = [];
        foreach ($this->publicFields as $property) {
            $reqAttributes = $property->getAttributes($attributeClass);
            if ($reqAttributes) {
                $result[] = $property->getName();
            }
        }
        return $result;
    }
}
