<?php

use \xTest\Entity\Attributes\Generators;
use \xTest\Entity\FieldGenerators;

return [
    Generators\CurrentDatetimeGenerator::class => FieldGenerators\SqlCurrentDatetime::class,
];
