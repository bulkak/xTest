<?php

namespace tests\unit\Repository;

use DI\Container;
use Psr\Log\NullLogger;
use tests\TestUser;
use xTest\Adapter\SqlAdapter;
use xTest\Repository\RepositoryException;
use xTest\Repository\RepositoryImpl;
use PHPUnit\Framework\TestCase;

class RepositoryImplTest extends TestCase
{
    private static Container $container;

    public function setUp(): void
    {
        /** @var Container $container */
        self::$container = (require(__DIR__ . '/../../../bootstrap_unit_test.php'));
    }

    public function testSaveNewValid()
    {
        //create
        $adapterMock = $this->createMock(SqlAdapter::class);
        $adapterMock->expects($this->exactly(3))
            ->method('getByColumn')
            ->willReturnOnConsecutiveCalls(
                false,
                false,
                [
                    'email' => 'bulkak-mail@yandex.ru',
                    'name'  => 'Denis Derkach',
                    'created' => '2022-07-03 20:16:00',
                    'id'    => 1,
                ],
            );
        $adapterMock->expects($this->any())
            ->method('getLastInsertId')
            ->willReturn(1);

        $metadata = self::$container->get('userMeta');
        $repository = new RepositoryImpl($adapterMock, $metadata, new NullLogger());

        $user = new TestUser();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis Derkach';
        $returnUser = clone $user;
        $returnUser->id = 1;
        $returnUser->notes = null;
        $returnUser->created = new \DateTime('2022-07-03 20:16:00');

        $useThatReturned = $repository->save($user);
        self::assertEquals($returnUser, $useThatReturned);
    }

    public function testSaveNewNotUnique()
    {
        //create
        $adapterMock = $this->createMock(SqlAdapter::class);
        $adapterMock->expects($this->exactly(2))
            ->method('getByColumn')
            ->willReturnOnConsecutiveCalls(
                [
                    'email' => 'bulkak-mail@yandex.ru',
                    'name'  => 'Denis Derkach',
                    'created' => '2022-07-03 20:16:00',
                    'id'    => 1,
                ],
                false
            );

        $metadata = self::$container->get('userMeta');
        $repository = new RepositoryImpl($adapterMock, $metadata, new NullLogger());

        $user = new TestUser();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis Derkach';
        $this->expectException(RepositoryException::class);
        $repository->save($user);
    }

    public function testUpdateValid()
    {
        //create
        $adapterMock = $this->createMock(SqlAdapter::class);
        $adapterMock->expects($this->exactly(2))
            ->method('getByColumn')
            ->willReturnOnConsecutiveCalls(
                [
                    'email' => 'bulkak-mail@yandex.ru',
                    'name'  => 'Denis Derkach',
                    'created' => '2022-07-03 20:16:00',
                    'id'    => 1,
                ],
                [
                    'email' => 'bulkak-mail@yandex.ru',
                    'name'  => 'Denis Derkach1',
                    'created' => '2022-07-03 20:16:00',
                    'id'    => 1,
                ],
            );

        $metadata = self::$container->get('userMeta');
        $repository = new RepositoryImpl($adapterMock, $metadata, new NullLogger());

        $user = new TestUser();
        $user->id = 1;
        $user->name = 'Denis Derkach1';

        $returnUser = clone $user;
        $returnUser->notes = null;
        $returnUser->created = new \DateTime('2022-07-03 20:16:00');
        $returnUser->email = 'bulkak-mail@yandex.ru';

        $useThatReturned = $repository->save($user);
        self::assertEquals($returnUser, $useThatReturned);
    }

    public function testUpdateNotUnique()
    {
        //create
        $adapterMock = $this->createMock(SqlAdapter::class);
        $adapterMock->expects($this->exactly(1))
            ->method('getByColumn')
            ->willReturnOnConsecutiveCalls(
                [
                    'email' => 'bulkak-mail@yandex.ru',
                    'name'  => 'Denis Derkach',
                    'created' => '2022-07-03 20:16:00',
                    'id'    => 1,
                ],
            );

        $metadata = self::$container->get('userMeta');
        $repository = new RepositoryImpl($adapterMock, $metadata, new NullLogger());

        $user = new TestUser();
        $user->id = 2;
        $user->name = 'Denis Derkach';

        $this->expectException(RepositoryException::class);
        $repository->save($user);
    }

}
