<?php

namespace xTest\Repository\FieldHydrators;

/**
 * format attribute of object
 */
interface FieldHydrator
{
    public static function hydrate($columnName, array $data);

    public static function extract($propName, object $object);
}