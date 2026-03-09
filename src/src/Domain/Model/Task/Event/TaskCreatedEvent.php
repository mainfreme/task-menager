<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Event;

final readonly class TaskCreatedEvent implements TaskDomainEventInterface
{
    public function eventType(): TaskEventType
    {
        return TaskEventType::Created;
    }

    public function __construct(
        public string $name,
        public ?string $description,
        public string $status,
        public int $assignedUserId,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'task' => [
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'assignedUserId' => $this->assignedUserId,
                'createdAt' => $this->createdAt->format('c'),
            ],
        ];
    }
}
