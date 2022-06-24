<?php
declare(strict_types=1);

namespace xTest;

use PDO;
use Psr\Container\ContainerInterface;
use xTest\Adapter\MysqlAdapterImpl;
use xTest\Adapter\SqlAdapter;
use xTest\Logger\AuditLogger;
use xTest\Logger\ErrorLogger;
use xTest\Repository\Repository;
use xTest\Repository\UserRepositoryImpl;
use xTest\Service\UserService;
use xTest\Service\UserServiceImpl;
use function DI\factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dbConf' => (require(__DIR__ . '/../resources/config/db.php')),
            SqlAdapter::class => factory(function (ContainerInterface $c) {
                $dbConf = $c->get('dbConf');
                return new MysqlAdapterImpl(new PDO(
                    $dbConf['connectionString'],
                    $dbConf['username'],
                    $dbConf['password']
                ));
            }),
            Repository::class => factory(function (ContainerInterface $c) {
                return new UserRepositoryImpl(
                    $c->get(SqlAdapter::class),
                    new AuditLogger()
                );
            }),
            UserService::class => factory(function (ContainerInterface $c) {
                return new UserServiceImpl(
                    $c->get(Repository::class),
                    new ErrorLogger()
                );
            }),
        ];
    }
}