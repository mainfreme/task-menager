<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Exception\TaskPersistenceException;
use App\Domain\Exception\UserNotFoundException;
use App\Domain\Model\Task\Task;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\TaskEntity;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTaskRepository implements TaskRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function get(int $id): Task
    {
        $task = $this->findById($id);

        if (null === $task) {
            throw TaskNotFoundException::withId($id);
        }

        return $task;
    }

    public function findById(int $id): ?Task
    {
        $entity = $this->entityManager->getRepository(TaskEntity::class)->find($id);

        return $entity?->toDomain();
    }

    /**
     * @return array<Task>
     */
    public function findByUserId(int $userId): array
    {
        $entities = $this->entityManager->createQueryBuilder()
            ->select('t')
            ->from(TaskEntity::class, 't')
            ->join('t.assignedUser', 'u')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (TaskEntity $entity): Task => $entity->toDomain(),
            $entities
        );
    }

    /**
     * @return array<Task>
     */
    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(TaskEntity::class)->findAll();

        return array_map(
            static fn (TaskEntity $entity): Task => $entity->toDomain(),
            $entities
        );
    }

    public function save(Task $task): void
    {
        try {
            $userEntity = $this->findUserEntityOrFail($task->getAssignedUserId());
            $taskEntity = TaskEntity::fromDomain($task, $userEntity);

            $this->entityManager->persist($taskEntity);
        } catch (UserNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw TaskPersistenceException::onSave($e);
        }
    }

    public function update(Task $task): void
    {
        try {
            $taskEntity = $this->entityManager->getRepository(TaskEntity::class)->find($task->getId());

            if (null === $taskEntity) {
                throw TaskNotFoundException::withId($task->getId());
            }

            $taskEntity->updateFromDomain($task);
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw TaskPersistenceException::onSave($e);
        }
    }

    public function delete(int $id): void
    {
        try {
            $entity = $this->entityManager->getRepository(TaskEntity::class)->find($id);

            if (null === $entity) {
                throw TaskNotFoundException::withId($id);
            }

            $this->entityManager->remove($entity);
        } catch (TaskNotFoundException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw TaskPersistenceException::onDelete($id, $e);
        }
    }

    public function flush(): void
    {
        try {
            $this->entityManager->flush();
        } catch (\Throwable $e) {
            throw TaskPersistenceException::onFlush($e);
        }
    }

    private function findUserEntityOrFail(int $userId): UserEntity
    {
        $userEntity = $this->entityManager->getRepository(UserEntity::class)->find($userId);

        if (null === $userEntity) {
            throw UserNotFoundException::withId($userId);
        }

        return $userEntity;
    }
}
