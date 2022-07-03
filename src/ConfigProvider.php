<?php
declare(strict_types=1);

namespace xTest;

use PDO;
use Psr\Container\ContainerInterface;
use xTest\Adapter\MysqlAdapterImpl;
use xTest\Adapter\SqlAdapter;
use xTest\Entity\EntityMetaBuilder;
use xTest\Entity\User;
use xTest\Logger\AuditLogger;
use xTest\Logger\ErrorLogger;
use xTest\Repository\EntityValidatorImp;
use xTest\Repository\Repository;
use xTest\Repository\RepositoryImpl;
use xTest\Service\UserService;
use xTest\Service\UserServiceImpl;
use function DI\factory;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dbConf' => (require(__DIR__ . '/../resources/config/db.php')),
            'validatorsMap' => (require(__DIR__ . '/../resources/config/validators_attribute_map.php')),
            'hydratorsMap' => (require(__DIR__ . '/../resources/config/hydrators_attribute_map.php')),
            'generatorsMap' => (require(__DIR__ . '/../resources/config/generators_attribute_map.php')),
            'userMeta' => factory(function (ContainerInterface $c) {
                return (new EntityMetaBuilder(
                    User::class,
                    $c->get('validatorsMap'),
                    $c->get('hydratorsMap'),
                    $c->get('generatorsMap'),
                ))->meta;
            }),
            AuditLogger::class => factory(function (ContainerInterface $c) {
                return new AuditLogger();
            }),
            ErrorLogger::class => factory(function (ContainerInterface $c) {
                return new ErrorLogger();
            }),
            SqlAdapter::class => factory(function (ContainerInterface $c) {
                $dbConf = $c->get('dbConf');
                return new MysqlAdapterImpl(new PDO(
                    $dbConf['connectionString'],
                    $dbConf['username'],
                    $dbConf['password'],
                ));
            }),
            Repository::class => factory(function (ContainerInterface $c) {
                return new RepositoryImpl(
                    $c->get(SqlAdapter::class),
                    $c->get('userMeta'),
                    $c->get(AuditLogger::class),
                );
            }),
            UserService::class => factory(function (ContainerInterface $c) {
                return new UserServiceImpl(
                    $c->get(Repository::class),
                    new EntityValidatorImp($c->get('userMeta')),
                    $c->get(ErrorLogger::class),
                );
            }),
        ];
    }
}