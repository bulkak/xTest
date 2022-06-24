<?php
declare(strict_types=1);

namespace xTest\Service\Dto;

class ServiceResponseError
{
    public function __construct(
        public readonly string $message,
        public readonly string $trace
    ){}
}