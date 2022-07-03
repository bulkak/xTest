<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class TableName {
    public function __construct(
        public string $tableName
    ){}
}