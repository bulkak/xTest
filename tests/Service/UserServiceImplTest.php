<?php

namespace Service;

use DI\Container;
use PHPUnit\Framework\Assert;
use xTest\Adapter\SqlAdapter;
use xTest\Entity\User;
use xTest\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceImplTest extends TestCase
{
    private static UserService $service;
    private static Container $container;

    public function setUp(): void
    {
        /** @var Container $container */
        self::$container = (require(__DIR__ . '/../../bootstrap_test.php'));
        self::$service = self::$container->get(UserService::class);
    }

    /**
     * @afterClass
     */
    public static function truncateTable()
    {
        $adapter = self::$container->get(SqlAdapter::class);
        $adapter->query('TRUNCATE TABLE users')->execute();
    }

    public function testSave()
    {
        //create
        $user = new User();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis Derkach';
        $response =  self::$service->save($user);
        Assert::assertEquals(true, $response->success);
        if ($response->success) {
            Assert::assertEquals($user->name, $response->data->name);
            Assert::assertNotNull($response->data->id);
        }
        // update
        $user = self::$service->getByIdentifier(1)->data;
        $userForUpdate = new User();
        $userForUpdate->id = $user->id;
        $userForUpdate->name = $user->name;
        $userForUpdate->email = $user->email;
        $userForUpdate->created = $user->created;
        $userForUpdate->notes = 'some notes about me';
        $response =  self::$service->save($userForUpdate);
        Assert::assertEquals(true, $response->success);
        if ($response->success) {
            Assert::assertEquals($userForUpdate->notes, $response->data->notes);
            Assert::assertNotNull($response->data->id);
        }
    }

    public function testValidators()
    {
        //too short name
        $user = new User();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis';
        $response =  self::$service->save($user);
        Assert::assertEquals(false, $response->success);

        //not unique name
        $user = new User();
        $user->email = 'bulkak-mail1@yandex.ru';
        $user->name = 'Denis Derkach';
        $response =  self::$service->save($user);
        Assert::assertEquals(false, $response->success);
        Assert::assertEquals(
            'Property {name} of entity {xTest\Entity\User} in not valid!',
            $response->errors[0]->message
        );
        Assert::assertEquals(
            'Property {name} of entity {xTest\Entity\User} is not unique!',
            $response->errors[1]->message
        );

        //not unique email
        $user = new User();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis Derkach1';
        $response =  self::$service->save($user);
        Assert::assertEquals(false, $response->success);

        //not valid email
        $user = new User();
        $user->email = 'bulkak-mail1@yandex..';
        $user->name = 'Denis Derkach1';
        $response =  self::$service->save($user);
        Assert::assertEquals(false, $response->success);

        //name black list words
        $user = new User();
        $user->email = 'bulkak-mail1@yandex.ru';
        $user->name = 'Denis stopWord_1';
        $response =  self::$service->save($user);
        Assert::assertEquals(false, $response->success);

        //email black list domains
        $user = new User();
        $user->email = 'bulkak-mail1@stopDomain_1.ru';
        $user->name = 'Denis Derkach1';
        $response =  self::$service->save($user);
        Assert::assertEquals(false, $response->success);

        //deleted greater than created
        $user = self::$service->getByIdentifier(1)->data;
        $userForUpdate = new User();
        $userForUpdate->id = $user->id;
        $userForUpdate->name = $user->name;
        $userForUpdate->email = $user->email;
        $userForUpdate->created = $user->created;
        $userForUpdate->deleted = $userForUpdate->created->modify('- 1 day');
        $response =  self::$service->save($userForUpdate);
        Assert::assertEquals(false, $response->success);
        Assert::assertEquals(
            'Property {deleted} of entity {xTest\\Entity\\User} is not valid!',
            $response->errors[1]->message
        );
    }
}
