<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;

final class ToDoTransitionStrategy implements StatusTransitionStrategyInterface
{
    public function getStatus(): TaskStatusEnum
    {
        return TaskStatusEnum::ToDo;
    }

    public function canTransitionTo(TaskStatusEnum $newStatus): bool
    {
        return TaskStatusEnum::InProgress === $newStatus;
    }
}
