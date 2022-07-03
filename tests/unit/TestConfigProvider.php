<?php
declare(strict_types=1);

namespace tests\unit;

use Psr\Container\ContainerInterface;
use tests\TestUser;
use xTest\Entity\EntityMetaBuilder;
use function DI\factory;

class TestConfigProvider
{
    public function __invoke(): array
    {
        $diConfig = (new \xTest\ConfigProvider())();
        $testConfig = [
            'dbConf' => [],
            'userMeta' => factory(function (ContainerInterface $c) {
                return (new EntityMetaBuilder(
                    TestUser::class,
                    $c->get('validatorsMap'),
                    $c->get('hydratorsMap'),
                    $c->get('generatorsMap'),
                ))->meta;
            }),
        ];
        return array_merge($diConfig, $testConfig);
    }
}