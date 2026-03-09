<?php

declare(strict_types=1);

namespace App\Application\Message;

use App\Domain\Repository\TaskEventRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class TaskEventRecordedHandler
{
    public function __construct(
        private readonly TaskEventRepositoryInterface $taskEventRepository,
    ) {
    }

    public function __invoke(TaskEventRecordedMessage $message): void
    {
        $this->taskEventRepository->record(
            $message->taskId,
            $message->eventType,
            $message->payload,
            $message->userId,
        );
    }
}
