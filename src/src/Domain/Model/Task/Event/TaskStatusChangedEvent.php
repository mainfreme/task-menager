<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Event;

final readonly class TaskStatusChangedEvent implements TaskDomainEventInterface
{
    public function eventType(): TaskEventType
    {
        return TaskEventType::StatusChanged;
    }

    /**
     * @param array<string, mixed> $task
     */
    public function __construct(
        public string $oldStatus,
        public string $newStatus,
        public array $task,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'oldStatus' => $this->oldStatus,
            'newStatus' => $this->newStatus,
            'task' => $this->task,
        ];
    }
}
