<?php
declare(strict_types=1);

namespace xTest\Repository;

use Psr\Log\LoggerInterface;
use stdClass;
use Throwable;
use xTest\Adapter\SqlAdapter;
use xTest\Entity\Entity;
use xTest\Entity\EntityMeta;

class RepositoryImpl implements Repository
{
    private array $errors = [];

    public function __construct(
        private readonly SqlAdapter $adapter,
        private readonly EntityMeta $entityMetadata,
        private readonly LoggerInterface $auditLogger,
    ) {}

    /**
     * @throws Throwable
     */
    public function save(Entity $entity): Entity
    {
        try {
            $valid = true;
            $this->adapter->beginTransaction();
            $dto = new stdClass();
            foreach ($this->entityMetadata->publicFields as $prop) {
                $propName = $prop->getName();
                $columnName = $this->entityMetadata->getColumnName($propName);
                if ($prop->isInitialized($entity)) {
                    if ($this->checkUniqueProperty($entity, $propName)) {
                        $dto->{$columnName} = $this->extractPropertyValue($propName, $entity);
                    } else {
                        $valid = false;
                    }
                }
            }
            if (!$valid) {
                throw new RepositoryException(
                    vsprintf(
                        'Entity {%s} data is not valid!',
                        [
                            $entity::class
                        ]
                    )
                );
            }
            return $this->saveDataToStore($dto, $entity);
        } catch (Throwable $e) {
            $this->adapter->rollbackTransaction();
            throw $e;
        } finally {
           $this->adapter->commitTransaction();
        }
    }

    public function getByIdentifier($id, Entity $entity): ?Entity
    {
        try {
            $rawData = $this->adapter->getByColumn(
                $this->entityMetadata->tableName,
                $this->entityMetadata->getColumnName($this->entityMetadata->identifierField),
                $id
            );
        } catch (Throwable $e) {
            throw new RepositoryException(
                'Problems with db connect or query execution: '
                    . $e->getMessage()
            );
        }

        if ($rawData) {
            return $this->hydrateEntity($rawData, $entity);
        }
        return null;
    }

    public function flushErrors(): array
    {
        $result = $this->errors;
        $this->errors = [];
        return $result;
    }

    protected function checkUniqueProperty(Entity $entity, $propName): bool
    {
        $isValid = true;
        $id = $this->getIdOfUniqueEntityField($entity, $propName);
        if (isset($id) &&
            (
                (
                    isset($entity->{$this->entityMetadata->identifierField})
                    && $id != $entity->{$this->entityMetadata->identifierField}
                )
                || !isset($entity->{$this->entityMetadata->identifierField})
            )
        ) {
            $isValid = false;
            $this->errors[] = vsprintf(
                'Property {%s} of entity {%s} is not unique!',
                [$propName, $entity::class]
            );
        }
        return $isValid;
    }

    /**
     * extract field value to store format
     */
    private function extractPropertyValue(string $propName, Entity $entity): string|int|null
    {
        if (isset($this->entityMetadata->hydrators[$propName])) {
            $hydrator = $this->entityMetadata->hydrators[$propName];
            return $hydrator::extract($propName, $entity);
        }
        return $entity->$propName;
    }

    private function saveDataToStore(stdClass $data, Entity $entity): Entity
    {
        try {
            $identifierColumn = $this->entityMetadata->getColumnName($this->entityMetadata->identifierField);
            if (isset($data->{$identifierColumn})) {
                $id = $data->{$identifierColumn};
                $this->adapter->update(
                    $this->entityMetadata->tableName,
                    (array)$data,
                    [$identifierColumn . ' = ?' => $data->{$identifierColumn}]
                );
                $this->auditLogger->info('UPDATE USER', (array)$data);
            } else {
                $data = $this->generateValuesBeforeInsert($data);
                $this->adapter->insert(
                    $this->entityMetadata->tableName,
                    (array)$data,
                );
                $id = $this->adapter->getLastInsertId();
                $this->auditLogger->info('CREATE USER', (array)$data);
            }
            return $this->getByIdentifier($id, new (get_class($entity)));
        } catch (Throwable $e) {
            throw new RepositoryException(
                'Problems with db connect or query execution: '
                . $e->getMessage()
            );
        }
    }

    private function generateValuesBeforeInsert(stdClass $data): stdClass
    {
        foreach ($this->entityMetadata->generators as $propertyName => $generator) {
            $data->{$propertyName} = $generator::generate();
        }
        return $data;
    }

    private function getIdOfUniqueEntityField(Entity $entity, $prop): ?int
    {
        if (in_array($prop, $this->entityMetadata->uniqueFields)) {
            $data = $this->getDataFromStoreByColumn($this->entityMetadata->getColumnName($prop), $entity->$prop);
            if ($data) {
                return $data[$this->entityMetadata->getColumnName($this->entityMetadata->identifierField)];
            }
        }
        return null;
    }

    private function getDataFromStoreByColumn(string $columnName, string $value): array | bool
    {
        return $this->adapter->getByColumn(
            $this->entityMetadata->tableName,
            $columnName,
            $value
        );
    }

    private function hydrateEntity(mixed $rawData, Entity $entity): Entity
    {
        foreach ($this->entityMetadata->publicFields as $prop) {
            $propName = $prop->getName();
            $columnName = $this->entityMetadata->getColumnName($propName);
            if (isset($this->entityMetadata->hydrators[$propName])) {
                $hydrator = $this->entityMetadata->hydrators[$propName];
                $entity->$propName = $hydrator::hydrate($columnName, $rawData);
            } else {
                $entity->$propName = $rawData[$columnName] ?? null;
            }
        }
        return $entity;
    }
}