<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Event;

final readonly class TaskUpdatedEvent implements TaskDomainEventInterface
{
    public function eventType(): TaskEventType
    {
        return TaskEventType::Updated;
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    public function __construct(
        public array $before,
        public array $after,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array
    {
        return [
            'before' => $this->before,
            'after' => $this->after,
        ];
    }
}
