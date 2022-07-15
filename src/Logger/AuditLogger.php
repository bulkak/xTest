<?php
declare(strict_types=1);

namespace xTest\Logger;

use Psr\Log\AbstractLogger;
use xTest\Tools\CliOutputColor;

class AuditLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        fwrite(
            STDOUT,
            PHP_EOL
            . CliOutputColor::BLUE->paint(
                time() . ': ' . $message
            )
        );
        fwrite(
            STDOUT,
            PHP_EOL
                . CliOutputColor::BLUE->paint(
                time() . ': ' . json_encode($context, JSON_PRETTY_PRINT)
                )
        );
    }

}