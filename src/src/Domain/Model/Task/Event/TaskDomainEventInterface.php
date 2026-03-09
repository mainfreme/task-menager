<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Event;

interface TaskDomainEventInterface
{
    public function eventType(): TaskEventType;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(): array;
}
