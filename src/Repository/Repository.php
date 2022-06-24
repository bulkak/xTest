<?php

namespace xTest\Repository;


use xTest\Entity\Entity;

/**
 * Knows how to store and read any Entity
 */
interface Repository
{
    /**
     * @param Entity $entity
     * @return Entity
     * @throws RepositoryException
     */
    public function save(Entity $entity): Entity;

    /**
     * @param $id
     * @param Entity $entity empty Entity instance for hydrate
     * @return Entity|null
     * @throws RepositoryException
     */
    public function getByIdentifier($id, Entity $entity): ?Entity;

    /**
     * return array of text errors and clear repository errors
     * @return array
     */
    public function flushErrors(): array;
}