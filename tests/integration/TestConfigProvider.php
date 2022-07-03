<?php
declare(strict_types=1);

namespace tests\integration;

use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;
use xTest\Logger\AuditLogger;
use xTest\Logger\ErrorLogger;
use function DI\factory;

class TestConfigProvider
{
    public function __invoke(): array
    {
        $diConfig = (new \xTest\ConfigProvider())();
        $testConfig = [
            'dbConf' => (require(__DIR__ . '/../../resources/config/test_db.php')),
            AuditLogger::class => factory(function (ContainerInterface $c) {
                return new NullLogger();
            }),
            ErrorLogger::class => factory(function (ContainerInterface $c) {
                return new NullLogger();
            }),
        ];
        return array_merge($diConfig, $testConfig);
    }
}