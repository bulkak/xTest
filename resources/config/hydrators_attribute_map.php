<?php

use \xTest\Entity\Attributes\Hydrators;
use \xTest\Entity\FieldHydrators;

return [
    Hydrators\DateTimeHydrator::class => FieldHydrators\MysqlDateTimeFieldHydrator::class,
];
