<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;

interface StatusTransitionStrategyInterface
{
    public function getStatus(): TaskStatusEnum;

    public function canTransitionTo(TaskStatusEnum $newStatus): bool;
}
