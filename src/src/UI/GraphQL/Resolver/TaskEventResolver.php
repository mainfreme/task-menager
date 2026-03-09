<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Domain\Repository\TaskEventRepositoryInterface;

final class TaskEventResolver
{
    public function __construct(
        private readonly TaskEventRepositoryInterface $taskEventRepository,
    ) {
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findByTaskId(int $taskId): array
    {
        return $this->taskEventRepository->findByTaskId($taskId);
    }
}
