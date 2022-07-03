<?php

use \xTest\Entity\Attributes\Validators;
use \xTest\Entity\FieldValidators;

return [
    Validators\EmailValidator::class => FieldValidators\EmailFieldValidator::class,
    Validators\BlackListContainsStringValidator::class => FieldValidators\BlackListContainsStringFieldValidator::class,
    Validators\GreaterThanFieldValidator::class => FieldValidators\GreaterThanFieldFieldValidator::class,
    Validators\RegExpValidator::class => FieldValidators\RegExpFieldValidator::class,
    Validators\StringLengthValidator::class => FieldValidators\StringLengthFieldValidator::class,
];
