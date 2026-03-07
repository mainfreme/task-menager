<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;

final class StatusTransitionResolver implements StatusTransitionResolverInterface
{
    /** @var StatusTransitionStrategyInterface[] */
    private array $strategies;

    /** @param StatusTransitionStrategyInterface[] $strategies */
    public function __construct(array $strategies)
    {
        $this->strategies = $strategies;
    }

    public function resolve(TaskStatusEnum $currentStatus): StatusTransitionStrategyInterface
    {
        foreach ($this->strategies as $strategy) {
            if ($strategy->getStatus() === $currentStatus) {
                return $strategy;
            }
        }

        throw new \InvalidArgumentException(
            sprintf('No transition strategy found for status "%s".', $currentStatus->value)
        );
    }
}
