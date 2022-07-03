<?php
declare(strict_types=1);

namespace tests;

use xTest\Entity\Attributes\Generators\CurrentDatetimeGenerator;
use xTest\Entity\Attributes\Hydrators\DateTimeHydrator;
use xTest\Entity\Attributes\Identifier;
use xTest\Entity\Attributes\Required;
use xTest\Entity\Attributes\TableName;
use xTest\Entity\Attributes\Unique;
use xTest\Entity\Attributes\Validators\EmailValidator;
use xTest\Entity\Attributes\Validators\GreaterThanFieldValidator;
use xTest\Entity\Attributes\Validators\RegExpValidator;
use xTest\Entity\Attributes\Validators\StringLengthValidator;
use xTest\Entity\Attributes\Validators\BlackListContainsStringValidator;
use xTest\Entity\Entity;

#[TableName('users')]
final class TestUser implements Entity
{
    #[Identifier]
    public int $id;
    #[
        Required,
        Unique,
        RegExpValidator('^[a-z0-9]+$^', false),
        StringLengthValidator(null, 8, null, false),
        BlackListContainsStringValidator('blackListWords'),
    ]
    public string $name;
    #[
        Required,
        Unique,
        EmailValidator(),
        BlackListContainsStringValidator('blackListDomains'),
    ]
    public string $email;
    #[
        CurrentDatetimeGenerator,
        DateTimeHydrator
    ]
    public \DateTime $created;
    #[
        GreaterThanFieldValidator('created'),
        DateTimeHydrator
    ]
    public ?\DateTime $deleted = null;
    public ?string $notes;
}
