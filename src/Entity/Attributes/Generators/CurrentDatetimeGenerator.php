<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes\Generators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CurrentDatetimeGenerator implements GeneratorAttribute
{
}