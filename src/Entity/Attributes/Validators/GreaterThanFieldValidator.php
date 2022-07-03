<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class GreaterThanFieldValidator implements ValidatorAttribute
{
    public function __construct(
        public string $compareWith,
        public bool $allowEmpty = true,
    ) {}
}
