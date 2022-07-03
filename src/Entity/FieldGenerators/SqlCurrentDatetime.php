<?php

namespace xTest\Entity\FieldGenerators;

use xTest\Tools\DateTimeFormat;

class SqlCurrentDatetime implements FiledGenerator
{
    /**
     * @inheritDoc
     */
    public static function generate(): string
    {
        return (new \DateTime())->format(DateTimeFormat::MYSQL_DATE_TIME);
    }
}