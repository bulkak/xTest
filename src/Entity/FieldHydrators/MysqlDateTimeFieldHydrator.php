<?php
declare(strict_types=1);

namespace xTest\Entity\FieldHydrators;

use xTest\Tools\DateTimeFormat;

class MysqlDateTimeFieldHydrator implements FieldHydrator
{
    public static function hydrate($columnName, array $data): ?\DateTime
    {
        if (isset($data[$columnName])) {
            try {
                return new \DateTime($data[$columnName]);
            } catch (\Throwable $e) {
                throw new HydratorException("Can't extract " . $columnName);
            }
        }
        return null;
    }

    public static function extract($propName, object $object): ?string
    {
        if (isset($object->{$propName})) {
            if (is_a($object->{$propName}, \DateTime::class)) {
                return $object->{$propName}->format(DateTimeFormat::MYSQL_DATE_TIME);
            } else {
                throw new HydratorException('Wrong object type for Datetime attribute!');
            }
        }
        return null;
    }
}