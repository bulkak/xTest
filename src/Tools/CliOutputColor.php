<?php

declare(strict_types=1);

namespace xTest\Tools;

enum CliOutputColor: string
{
    case RED = "\033[91m";
    case GREEN = "\033[92m";
    case YELLOW = "\033[93m";
    case BLUE = "\033[94m";

    public function paint(string $data): string
    {
        return $this->value . $data . "\033[0m";
    }
}