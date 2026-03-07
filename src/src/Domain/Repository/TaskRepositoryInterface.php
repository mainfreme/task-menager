<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Exception\TaskPersistenceException;
use App\Domain\Model\Task\TaskAggregate;

interface TaskRepositoryInterface
{
    /**
     * @throws TaskNotFoundException
     */
    public function get(int $id): TaskAggregate;

    public function findById(int $id): ?TaskAggregate;

    /**
     * @return array<TaskAggregate>
     */
    public function findByUserId(int $userId): array;

    /**
     * @return array<TaskAggregate>
     */
    public function findAll(): array;

    /**
     * @throws TaskPersistenceException
     */
    public function save(TaskAggregate $task): void;

    /**
     * @throws TaskPersistenceException
     */
    public function update(TaskAggregate $task): void;

    /**
     * @throws TaskNotFoundException
     * @throws TaskPersistenceException
     */
    public function delete(int $id): void;

    /**
     * @throws TaskPersistenceException
     */
    public function flush(): void;
}
