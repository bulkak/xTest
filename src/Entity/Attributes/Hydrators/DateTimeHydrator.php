<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes\Hydrators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DateTimeHydrator implements HydratorAttribute
{}
