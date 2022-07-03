<?php

namespace tests\integration\Service;

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
        self::$container = (require(__DIR__ . '/../../../bootstrap_integration_test.php'));
        self::$service = self::$container->get(UserService::class);
    }

    /**
     * @afterClass
     */
    public static function truncateTable()
    {
        $adapter = self::$container->get(SqlAdapter::class);
        $adapter->truncateTable('users');
    }

    public function testSave()
    {
        //create
        $user = new User();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis Derkach';
        $response =  self::$service->save($user);
        self::assertEquals(true, $response->success);
        if ($response->success) {
            self::assertEquals($user->name, $response->data->name);
            self::assertNotNull($response->data->id);
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
        self::assertEquals(true, $response->success);
        if ($response->success) {
            self::assertEquals($userForUpdate->notes, $response->data->notes);
            self::assertNotNull($response->data->id);
        }
    }

    public function testValidators()
    {
        //create
        $user = new User();
        $user->email = 'bulkak-mail1@yandex.ru';
        $user->name = 'Denis1 Derkach1';
        $response =  self::$service->save($user);
        if ($response->success) {
            self::assertEquals($user->name, $response->data->name);
            self::assertNotNull($response->data->id);
        }

        //too short name
        $user = new User();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis';
        $response =  self::$service->save($user);
        self::assertEquals(false, $response->success);

        //not unique name
        $user = new User();
        $user->email = 'bulkak-mail2@yandex.ru';
        $user->name = 'Denis1 Derkach1';
        $response =  self::$service->save($user);
        self::assertEquals(false, $response->success);
        self::assertEquals(
            'Entity {xTest\Entity\User} data is not valid!',
            $response->errors[0]->message
        );
        self::assertEquals(
            'Property {name} of entity {xTest\Entity\User} is not unique!',
            $response->errors[1]->message
        );

        //not unique email
        $user = new User();
        $user->email = 'bulkak-mail1@yandex.ru';
        $user->name = 'Denis Derkach1';
        $response =  self::$service->save($user);
        self::assertEquals(false, $response->success);
//
//        //not valid email
        $user = new User();
        $user->email = 'bulkak-mail1@yandex..';
        $user->name = 'Denis Derkach1';
        $response =  self::$service->save($user);
        self::assertEquals(false, $response->success);
//
//        //name black list words
        $user = new User();
        $user->email = 'bulkak-mail1@yandex.ru';
        $user->name = 'Denis stopWord_1';
        $response =  self::$service->save($user);
        self::assertEquals(false, $response->success);
//
//        //email black list domains
        $user = new User();
        $user->email = 'bulkak-mail1@stopDomain_1.ru';
        $user->name = 'Denis Derkach1';
        $response =  self::$service->save($user);
        self::assertEquals(false, $response->success);
//
//        //deleted greater than created
        $user = self::$service->getByIdentifier(1)->data;
        $userForUpdate = new User();
        $userForUpdate->id = $user->id;
        $userForUpdate->name = $user->name;
        $userForUpdate->email = $user->email;
        $userForUpdate->created = $user->created;
        $userForUpdate->deleted = $userForUpdate->created->modify('- 1 day');
        $response =  self::$service->save($userForUpdate);
        self::assertEquals(false, $response->success);
        self::assertEquals(
            'Property {deleted} of entity {xTest\\Entity\\User} is not valid!',
            $response->errors[0]->message
        );
    }
}