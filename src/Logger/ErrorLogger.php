<?php
declare(strict_types=1);

namespace xTest\Logger;

use Psr\Log\AbstractLogger;
use xTest\Tools\CliOutputColor;

class ErrorLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        fwrite(
            STDOUT,
            PHP_EOL . CliOutputColor::RED->paint(
                time() . ': ' . $level . PHP_EOL . $message
            )
        );
        fwrite(
            STDOUT,
             PHP_EOL . CliOutputColor::RED->paint(
                 json_encode($context, JSON_PRETTY_PRINT)
             )
        );
    }

}