<?php
declare(strict_types=1);

namespace xTest\Entity\Attributes\Validators;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class StringLengthValidator implements ValidatorAttribute
{
    public function __construct(
        public ?int $max = null,
        public ?int $min = null,
        public ?int $is = null,
        public bool $allowEmpty = true,
        public ?string $encoding = null
    ){}
}
