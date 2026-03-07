<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;

final class DoneTransitionStrategy implements StatusTransitionStrategyInterface
{
    public function getStatus(): TaskStatusEnum
    {
        return TaskStatusEnum::Done;
    }

    public function canTransitionTo(TaskStatusEnum $newStatus): bool
    {
        return TaskStatusEnum::ToDo === $newStatus;
    }
}
