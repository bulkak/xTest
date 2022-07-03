<?php
declare(strict_types=1);

namespace xTest\Service\Dto;

use xTest\Entity\Entity;

class ServiceResponse
{
    /**
     * @param $errors ServiceResponseError[]
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?Entity $data,
        public readonly array $errors
    ){}
}