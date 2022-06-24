<?php

namespace xTest\Service;

use xTest\Entity\User;
use xTest\Service\Dto\ServiceResponse;

interface UserService
{
    /**
     * @param User $entity
     * @return ServiceResponse
     */
    public function save(User $entity): ServiceResponse;

    /**
     * @param $id
     * @return ServiceResponse
     */
    public function getByIdentifier($id): ServiceResponse;
}