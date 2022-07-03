<?php

namespace xTest\Entity\FieldGenerators;

interface FiledGenerator
{
    /**
     * generates default value for attribute
     */
    public static function generate(): mixed;
}