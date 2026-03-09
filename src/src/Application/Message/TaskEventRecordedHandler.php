<?php

declare(strict_types=1);

namespace App\Application\Message;

use App\Domain\Repository\TaskEventRecorderInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class TaskEventRecordedHandler
{
    public function __construct(
        private readonly TaskEventRecorderInterface $eventRecorder,
    ) {
    }

    public function __invoke(TaskEventRecordedMessage $message): void
    {
        $this->eventRecorder->record(
            $message->taskId,
            $message->eventType,
            $message->payload,
            $message->userId,
        );
    }
}
