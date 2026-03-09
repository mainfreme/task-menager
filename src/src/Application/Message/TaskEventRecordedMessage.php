<?php

declare(strict_types=1);

namespace App\Application\Message;

use App\Domain\Model\Task\Event\TaskEventType;

final readonly class TaskEventRecordedMessage
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public int $taskId,
        public TaskEventType $eventType,
        public array $payload,
        public int $userId,
    ) {
    }
}
