<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;

interface StatusTransitionResolverInterface
{
    public function resolve(TaskStatusEnum $currentStatus): StatusTransitionStrategyInterface;
}
