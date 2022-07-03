<?php
declare(strict_types=1);

namespace xTest\Entity;

use ReflectionProperty;
use xTest\Entity\FieldGenerators\FiledGenerator;
use xTest\Entity\FieldHydrators\FieldHydrator;
use xTest\Entity\FieldValidators\FieldValidator;

class EntityMeta
{
    /**
     * @param ReflectionProperty[] $publicFields
     * @param string $identifierField
     * @param string $tableName
     * Key - name of property, value - array of instances of Validator implemented class
     * @param FieldValidator[][] $validators
     * Key - name of property, value - instance of FieldHydrator implemented class
     * @param FieldHydrator[] $hydrators
     * Key - name of property, value - instance of FiledGenerator implemented class. It's generate value before insert
     * @param FiledGenerator[] $generators
     * Array of required properties of Entity that should be initialized, for check before save
     * @param array $requiredFields
     * Array of unique properties of Entity, for check before save
     * @param array $uniqueFields
     */
    public function __construct(
        public readonly array $publicFields,
        public readonly string $identifierField,
        public readonly string $tableName,
        public readonly array $validators,
        public readonly array $hydrators,
        public readonly array $generators,
        public readonly array $requiredFields,
        public readonly array $uniqueFields,
    ){}

    public function getColumnName(string $attributeName): string
    {
        static $cache = [];
        if (!isset($cache[$attributeName])) {
            $cache[$attributeName] = self::delimit($attributeName);
        }
        return $cache[$attributeName];
    }
    /**
     * Expects a CamelCasedInputString, and produces a lower_case_delimited_string.
     */
    private static function delimit(string $string): string
    {
        return mb_strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_' . '\\1', $string));
    }
}
