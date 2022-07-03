<?php

namespace tests\unit\Repository;

use DI\Container;
use tests\TestUser;
use xTest\Repository\EntityValidator;
use xTest\Repository\EntityValidatorImp;
use PHPUnit\Framework\TestCase;

class EntityValidatorTest extends TestCase
{
    private static EntityValidator $validator;

    public function setUp(): void
    {
        /** @var Container $container */
        $container = (require(__DIR__ . '/../../../bootstrap_unit_test.php'));
        $meta = $container->get('userMeta');
        self::$validator = new EntityValidatorImp($meta);
    }

    public function testValidators()
    {
        //too short name
        $user = new TestUser();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis';
        $result = self::$validator->validate($user);
        $errors = self::$validator->flushErrors();
        self::assertEquals(false, $result);
        self::assertEquals(['Property {name} of entity {tests\TestUser} is not valid!'], $errors);

        //not valid email
        $user = new TestUser();
        $user->email = 'bulkak-mail1@yandex..';
        $user->name = 'Denis Derkach';
        $result = self::$validator->validate($user);
        $errors = self::$validator->flushErrors();
        self::assertEquals(false, $result);
        self::assertEquals(['Property {email} of entity {tests\TestUser} is not valid!'], $errors);

        //name black list words
        $user = new TestUser();
        $user->email = 'bulkak-mail@yandex.ru';
        $user->name = 'Denis stopWord_1';
        $result = self::$validator->validate($user);
        $errors = self::$validator->flushErrors();
        self::assertEquals(false, $result);
        self::assertEquals(['Property {name} of entity {tests\TestUser} is not valid!'], $errors);

        //email black list domain
        $user = new TestUser();
        $user->email = 'bulkak-mail1@stopDomain_1.ru';
        $user->name = 'Denis Derkach';
        $result = self::$validator->validate($user);
        $errors = self::$validator->flushErrors();
        self::assertEquals(false, $result);
        self::assertEquals(['Property {email} of entity {tests\TestUser} is not valid!'], $errors);

        //deleted greater than created
        $userForUpdate = new TestUser();
        $userForUpdate->id = 1;
        $userForUpdate->name = 'Denis Derkach';
        $userForUpdate->email = 'bulkak-mail@yandex.ru';
        $userForUpdate->created = new \DateTime('2022-07-03 21:37:00');
        $userForUpdate->deleted = $userForUpdate->created->modify('- 1 day');
        $result = self::$validator->validate($userForUpdate);
        $errors = self::$validator->flushErrors();
        self::assertEquals(false, $result);
        self::assertEquals(['Property {deleted} of entity {tests\TestUser} is not valid!'], $errors);
    }
}
