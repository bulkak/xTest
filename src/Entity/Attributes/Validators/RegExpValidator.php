<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RegExpValidator implements ValidatorAttribute
{
    public function __construct(
        public string $pattern,
        public bool $allowEmpty = true,
        public $not = false,
    ){}
}
