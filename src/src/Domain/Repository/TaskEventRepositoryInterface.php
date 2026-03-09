<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Task\Event\TaskEventType;

interface TaskEventRepositoryInterface
{
    /**
     * @return array<array<string, mixed>>
     */
    public function findByTaskId(int $taskId): array;

    /**
     * @param array<string, mixed> $payload
     */
    public function record(int $taskId, TaskEventType $type, array $payload, int $userId): void;
}
