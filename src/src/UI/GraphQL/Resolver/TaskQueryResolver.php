<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Domain\Model\Task\TaskAggregate;
use App\Domain\Repository\TaskRepositoryInterface;

final class TaskQueryResolver
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $task = $this->taskRepository->findById($id);

        if (null === $task) {
            return null;
        }

        return $this->toGraphQL($task);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findAll(): array
    {
        return array_map(
            fn (TaskAggregate $task): array => $this->toGraphQL($task),
            $this->taskRepository->findAll(),
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findByUserId(int $userId): array
    {
        return array_map(
            fn (TaskAggregate $task): array => $this->toGraphQL($task),
            $this->taskRepository->findByUserId($userId),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toGraphQL(TaskAggregate $task): array
    {
        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->value,
            'assignedUserId' => $task->getAssignedUserId(),
            'createdAt' => $task->getCreatedAt()->format('c'),
            'updatedAt' => $task->getUpdatedAt()->format('c'),
        ];
    }
}
