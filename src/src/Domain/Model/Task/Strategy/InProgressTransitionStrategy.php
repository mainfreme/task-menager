<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;

final class InProgressTransitionStrategy implements StatusTransitionStrategyInterface
{
    public function getStatus(): TaskStatusEnum
    {
        return TaskStatusEnum::InProgress;
    }

    public function canTransitionTo(TaskStatusEnum $newStatus): bool
    {
        return in_array($newStatus, [
            TaskStatusEnum::ToDo,
            TaskStatusEnum::Done,
        ], true);
    }
}
