<?php
declare(strict_types=1);

namespace xTest\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;
use xTest\Entity\User;
use xTest\Logger\AuditLogger;
use xTest\Repository\Repository;
use xTest\Repository\RepositoryException;
use xTest\Service\Dto\ServiceResponse;
use xTest\Service\Dto\ServiceResponseError;

final class UserServiceImpl implements UserService
{
    public function __construct(
        private readonly Repository $userRepository,
        private readonly LoggerInterface $errorLogger
    ){}

    public function save(User $entity): ServiceResponse
    {
        $errors = [];
        try {
            $user = $this->userRepository->save($entity);
            $success = true;
        } catch (Throwable $e) {
            $error = new ServiceResponseError($e->getMessage(), $e->getTraceAsString());
            $errors[] = $error;
            $success = false;
            if (!is_a($e, RepositoryException::class)) {
                $this->logError($e, ['UserService::save', $entity]);
            }
        } finally {
            $errors = $this->flushRepositoryErrors($success, $errors);
            return new ServiceResponse($success, $user ?? null,  $errors);
        }
    }

    public function getByIdentifier($id): ServiceResponse
    {
        $errors = [];
        try {
            $user = $this->userRepository->getByIdentifier($id, new User());
            $success = (bool)$user ?? false;
        } catch (Throwable $e) {
            $error = new ServiceResponseError($e->getMessage(), $e->getTraceAsString());
            $errors[] = $error;
            $success = false;
            if (!is_a($e, RepositoryException::class)) {
                $this->logError($e, ['UserService::getByIdentifier', $id]);
            }
        } finally {
            $errors = $this->flushRepositoryErrors($success, $errors);
            return new ServiceResponse($success, $user ?? null,  $errors);
        }
    }

    private function flushRepositoryErrors($success, array $errors): array
    {
        if (!$success) {
            foreach ($this->userRepository->flushErrors() as $error) {
                $error = new ServiceResponseError($error, '');
                $errors[] = $error;
            }
        }
        return $errors;
    }

    private function logError(Throwable $e, array $context): void
    {
        $this->errorLogger->critical(
            $e->getMessage() . PHP_EOL . $e->getTraceAsString(),
            $context
        );
    }
}
