<?php

namespace xTest\Repository;

use xTest\Entity\Entity;

interface EntityValidator
{
    public function validate(Entity $entity): bool;

    public function flushErrors(): array;
}