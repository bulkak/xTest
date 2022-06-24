<?php
declare(strict_types=1);

namespace xTest\Service\Dto;

use xTest\Entity\Entity;

class ServiceResponse
{
    public function __construct(
        public readonly bool $success,
        public readonly ?Entity $data,
        /**
         * @var $errors ServiceResponseError[]
         */
        public readonly array $errors
    ){}
}