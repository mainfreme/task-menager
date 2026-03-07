<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Exception\TaskNotFoundException;
use App\Domain\Exception\TaskPersistenceException;
use App\Domain\Model\Task\Task;

interface TaskRepositoryInterface
{
    /**
     * @throws TaskNotFoundException
     */
    public function get(int $id): Task;

    public function findById(int $id): ?Task;

    /**
     * @return array<Task>
     */
    public function findByUserId(int $userId): array;

    /**
     * @return array<Task>
     */
    public function findAll(): array;

    /**
     * @throws TaskPersistenceException
     */
    public function save(Task $task): void;

    /**
     * @throws TaskPersistenceException
     */
    public function update(Task $task): void;

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
