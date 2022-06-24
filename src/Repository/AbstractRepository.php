<?php
declare(strict_types=1);

namespace xTest\Repository;

use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionProperty;
use stdClass;
use xTest\Adapter\SqlAdapter;
use xTest\Entity\Entity;
use xTest\Repository\FieldHydrators\FieldHydrator;
use xTest\Repository\Factory\ValidatorFactory;

abstract class AbstractRepository implements Repository
{
    protected string $tableName;
    protected string $identifierField;
    protected string $identifierColumn;
    /**
     * Array of unique properties of Entity, for check before save
     */
    protected array $uniqueFields = [];
    /**
     * Array of required properties of Entity that should be initialized, for check before save
     */
    protected array $requiredFields = [];
    /**
     * Key - name of table column, value - name of FieldHydrator implemented class
     */
    protected array $fieldHydrators = [];
    /**
     * Key - name of table column, value - name of Validator implemented class
     */
    protected array $fieldsValidators = [];

    protected array $errors = [];

    public function __construct(
        public SqlAdapter $adapter,
        private readonly LoggerInterface $auditLogger
    ) {}

    public function save(Entity $entity): Entity
    {
        $reflect = new ReflectionClass($entity);
        $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        $dto = new stdClass();
        foreach ($props as $prop) {
            $propName = $prop->getName();
            $columnName = self::delimit($propName);
            if ($prop->isInitialized($entity)) {
                if ($this->validateProperty($entity, $propName)) {
                    $dto->{$columnName} = $this->extractPropertyValue($propName, $entity);
                } else {
                    throw new RepositoryException(
                        vsprintf(
                            'Property {%s} of entity {%s} in not valid!',
                            [
                                $propName,
                                $entity::class
                            ]
                        )
                    );
                }
            } elseif ($this->checkIsColumnRequired($columnName)) {
                throw new RepositoryException(
                    vsprintf(
                        'Property {%s} of entity {%s} must be initialized!',
                        [
                            $propName,
                            $entity::class
                        ]
                    )
                );
            }
        }

        return $this->saveDataToStore($dto, $entity);
    }

    public function getByIdentifier($id, Entity $entity): ?Entity
    {
        try {
            $rawData = $this->adapter->select(
                $this->tableName,
                [
                    $this->identifierColumn => $id
                ]
            )->fetch(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            throw new RepositoryException(
                'Problems with db connect or query execution: '
                    . $e->getMessage()
            );
        }

        if ($rawData) {
            $reflect = new ReflectionClass($entity);
            $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($props as $prop) {
                $propName = $prop->getName();
                $columnName = self::delimit($propName);
                if (isset($this->fieldHydrators[$propName])) {
                    /** @var FieldHydrator $hydrator */
                    $hydrator = $this->fieldHydrators[$propName];
                    $entity->$propName = $hydrator::hydrate($columnName, $rawData);
                } else {
                    $entity->$propName = $rawData[$columnName] ?? null;
                }
            }
            return $entity;
        }
        return null;
    }

    public function flushErrors(): array
    {
        $result = $this->errors;
        $this->errors = [];
        return $result;
    }

    protected function validateProperty(Entity $entity, $propName): bool
    {
        $isValid = true;
        if ($this->validateFieldValue($entity, $propName)) {
            $id = $this->getIdOfUniqueEntityField($entity, $propName);
            if (isset($id) &&
                ((isset($entity->{$this->identifierField})
                    && $id != $entity->{$this->identifierField}) || !isset($entity->{$this->identifierField})))
            {
                $isValid = false;
                $this->errors[] = vsprintf(
                    'Property {%s} of entity {%s} is not unique!',
                    [$propName, $entity::class]
                );
            }
        } else {
            $isValid = false;
            $this->errors[] = vsprintf('Property {%s} of entity {%s} is not valid!', [$propName, $entity::class]);
        }
        return $isValid;
    }

    protected function validateFieldValue(Entity $entity, string $propName): bool
    {
        $validators = $this->fieldsValidators[$propName] ?? [];

        foreach ($validators as $validatorConf) {
            $validator = ValidatorFactory::createInstance($validatorConf['validator'], $validatorConf['config']);
            if (!$validator->validateAttribute($entity, $propName)) {
                return false;
            }
        }
        return true;
    }

    /**
     * extract field value to store format
     */
    protected function extractPropertyValue(string $propName, Entity $entity): string|int|null
    {
        if (isset($this->fieldHydrators[$propName])) {
            /** @var FieldHydrator $hydrator */
            $hydrator = $this->fieldHydrators[$propName];
            return $hydrator::extract($propName, $entity);
        }
        return $entity->$propName;
    }

    protected function checkIsColumnRequired($columnName): bool
    {
        return in_array($columnName, $this->requiredFields);
    }

    private function saveDataToStore(stdClass $data, Entity $entity): Entity
    {
        try {
            if (isset($data->{$this->identifierColumn})) {
                $id = $data->{$this->identifierColumn};
                $this->adapter->update(
                    $this->tableName,
                    (array)$data,
                    [$this->identifierColumn . ' = ?' => $data->{$this->identifierColumn}]
                );
                $this->auditLogger->info('UPDATE USER', (array)$data);
            } else {
                $data = $this->generateValuesBeforeInsert($data);
                $this->adapter->insert(
                    $this->tableName,
                    (array)$data,
                );
                $id = $this->adapter->query('SELECT LAST_INSERT_ID()')->fetchColumn();
                $this->auditLogger->info('CREATE USER', (array)$data);
            }
            return $this->getByIdentifier($id, new (get_class($entity)));
        } catch (\Throwable $e) {
            throw new RepositoryException(
                'Problems with db connect or query execution: '
                . $e->getMessage()
            );
        }
    }

    protected function getIdOfUniqueEntityField(Entity $entity, $prop): ?int
    {
        if (in_array($prop, $this->uniqueFields)) {
            $data = $this->getDataFromStoreByColumn(self::delimit($prop), $entity->$prop);
            if ($data) {
                return $data[$this->identifierColumn];
            }
        }
        return null;
    }

    private function getDataFromStoreByColumn(string $columnName, string $value): array|bool
    {
        $where[$columnName] = $value;
        return $this->adapter->select(
            $this->tableName,
            $where
        )->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * If column value needs to be generated on insert do it here
     */
    protected abstract function generateValuesBeforeInsert(stdClass $data): stdClass;

    /**
     * Expects a CamelCasedInputString, and produces a lower_case_delimited_string.
     */
    protected static function delimit(string $string, string $delimiter = '_'): string
    {
        return mb_strtolower(preg_replace('/(?<=\\w)([A-Z])/', $delimiter . '\\1', $string));
    }
}