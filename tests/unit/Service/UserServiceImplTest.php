<?php

namespace tests\unit\Service;

use DI\Container;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use xTest\Entity\User;
use xTest\Repository\EntityValidator;
use xTest\Repository\Repository;
use PHPUnit\Framework\TestCase;
use xTest\Repository\RepositoryException;
use xTest\Service\Dto\ServiceResponseError;
use xTest\Service\UserServiceImpl;

class UserServiceImplTest extends TestCase
{
    public function testSaveSuccess()
    {
        $user = new User();
        $repositoryMock = $this->createMock(Repository::class);
        $validatorMock = $this->createMock(EntityValidator::class);
        $service = new UserServiceImpl($repositoryMock, $validatorMock, new NullLogger());

        $validatorMock
            ->expects($this->exactly(1))
            ->method('validate')
            ->willReturn(true);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('save')
            ->willReturn($user);

        $response = $service->save($user);

        self::assertEquals(true, $response->success);
        self::assertEquals($user, $response->data);
        self::assertEquals([], $response->errors);
    }

    public function testSaveWithRepositoryError()
    {
        $user = new User();
        $repositoryMock = $this->createMock(Repository::class);
        $validatorMock = $this->createMock(EntityValidator::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $service = new UserServiceImpl($repositoryMock, $validatorMock, $loggerMock);

        $validatorMock
            ->expects($this->exactly(1))
            ->method('validate')
            ->willReturn(true);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('flushErrors')
            ->willReturn(['error message']);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('save')
            ->willThrowException(new RepositoryException('error'));
        $loggerMock
            ->expects($this->exactly(0))
            ->method('critical');

        $response = $service->save($user);

        self::assertEquals(false, $response->success);
        self::assertEquals(null, $response->data);
        $err1 = new ServiceResponseError('error', '');
        $err2 = new ServiceResponseError('error message', '');

        self::assertEquals($err1->message, $response->errors[0]->message);
        self::assertEquals($err2->message, $response->errors[1]->message);
    }

    public function testSaveWithCriticalError()
    {
        $user = new User();
        $repositoryMock = $this->createMock(Repository::class);
        $validatorMock = $this->createMock(EntityValidator::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $service = new UserServiceImpl($repositoryMock, $validatorMock, $loggerMock);

        $validatorMock
            ->expects($this->exactly(1))
            ->method('validate')
            ->willReturn(true);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('flushErrors')
            ->willReturn(['error message']);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('save')
            ->willThrowException(new \RuntimeException('error'));
        $loggerMock
            ->expects($this->exactly(1))
            ->method('critical');

        $response = $service->save($user);

        self::assertEquals(false, $response->success);
        self::assertEquals(null, $response->data);
        $err1 = new ServiceResponseError('error', '');
        $err2 = new ServiceResponseError('error message', '');

        self::assertEquals($err1->message, $response->errors[0]->message);
        self::assertEquals($err2->message, $response->errors[1]->message);
    }

    public function testGetSuccess()
    {
        $user = new User();
        $repositoryMock = $this->createMock(Repository::class);
        $validatorMock = $this->createMock(EntityValidator::class);
        $service = new UserServiceImpl($repositoryMock, $validatorMock, new NullLogger());

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('getByIdentifier')
            ->willReturn($user);

        $response = $service->getByIdentifier($user);

        self::assertEquals(true, $response->success);
        self::assertEquals($user, $response->data);
        self::assertEquals([], $response->errors);
    }

    public function testGetRepositoryError()
    {
        $user = new User();
        $repositoryMock = $this->createMock(Repository::class);
        $validatorMock = $this->createMock(EntityValidator::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $service = new UserServiceImpl($repositoryMock, $validatorMock, $loggerMock);

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('flushErrors')
            ->willReturn(['error message']);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('getByIdentifier')
            ->willThrowException(new RepositoryException('error'));

        $response = $service->getByIdentifier($user);

        self::assertEquals(false, $response->success);
        self::assertEquals(null, $response->data);

        $err1 = new ServiceResponseError('error', '');
        $err2 = new ServiceResponseError('error message', '');
        self::assertEquals($err1->message, $response->errors[0]->message);
        self::assertEquals($err2->message, $response->errors[1]->message);
    }

    public function testGetCriticalError()
    {
        $user = new User();
        $repositoryMock = $this->createMock(Repository::class);
        $validatorMock = $this->createMock(EntityValidator::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $service = new UserServiceImpl($repositoryMock, $validatorMock, $loggerMock);

        $repositoryMock
            ->expects($this->exactly(1))
            ->method('flushErrors')
            ->willReturn(['error message']);
        $repositoryMock
            ->expects($this->exactly(1))
            ->method('getByIdentifier')
            ->willThrowException(new \RuntimeException('error'));
        $loggerMock
            ->expects($this->exactly(1))
            ->method('critical');

        $response = $service->getByIdentifier($user);

        self::assertEquals(false, $response->success);
        self::assertEquals(null, $response->data);

        $err1 = new ServiceResponseError('error', '');
        $err2 = new ServiceResponseError('error message', '');
        self::assertEquals($err1->message, $response->errors[0]->message);
        self::assertEquals($err2->message, $response->errors[1]->message);
    }
}
