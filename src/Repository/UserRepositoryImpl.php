<?php
declare(strict_types=1);

namespace xTest\Repository;

use stdClass;
use xTest\Repository\FieldHydrators\MysqlDateTimeFieldHydrator;
use xTest\Repository\FieldValidators\EmailValidator;
use xTest\Repository\FieldValidators\MoreThanValidator;
use xTest\Repository\FieldValidators\RegExpValidator;
use xTest\Repository\FieldValidators\BlackListContainsStringValidator;
use xTest\Repository\FieldValidators\StringLenghtValidator;
use xTest\Tools\DateTimeFormat;

/**
 * knows how to store and read User entity
 */
final class UserRepositoryImpl extends AbstractRepository
{
    protected string $tableName = 'users';
    protected string $identifierField = 'id';
    protected string $identifierColumn = 'id';

    protected array $uniqueFields = [
        'name',
        'email',
    ];
    protected array $requiredFields = [
        'name',
        'email',
    ];
    protected array $fieldHydrators = [
        'created' => MysqlDateTimeFieldHydrator::class,
        'deleted' => MysqlDateTimeFieldHydrator::class,
    ];
    protected array $fieldsValidators = [
        'name' => [
            [
                'validator' => RegExpValidator::class,
                'config' => [
                    'pattern' => '^[a-z0-9]+$^',
                    'allowEmpty' => false,
                ]
            ],
            [
                'validator' => StringLenghtValidator::class,
                'config' => [
                    'min' => 8,
                    'allowEmpty' => false,
                ]
            ],
            [
                'validator' => BlackListContainsStringValidator::class,
                'config' => [
                    'blackListSource' => 'blackListWords.php',
                ]
            ],
        ],
        'email' => [
            [
                'validator' => EmailValidator::class,
                'config' => []
            ],
            [
                'validator' => BlackListContainsStringValidator::class,
                'config' => [
                    'blackListSource' => 'blackListDomains.php',
                ]
            ],
        ],
        'deleted' => [
            [
                'validator' => MoreThanValidator::class,
                'config' => [
                    'compareWith' => 'created',
                ]
            ]
        ],
    ];

    protected function generateValuesBeforeInsert(stdClass $data): stdClass
    {
        $data->created = (new \DateTime())->format(DateTimeFormat::MYSQL_DATE_TIME);
        return $data;
    }
}