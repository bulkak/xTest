<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EmailValidator implements ValidatorAttribute
{
}
