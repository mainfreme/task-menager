<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\Model\Enum\TaskStatusEnum;

final class InvalidStatusTransitionException extends \DomainException
{
    public function __construct(TaskStatusEnum $from, TaskStatusEnum $to)
    {
        parent::__construct(
            sprintf('Cannot transition task status from "%s" to "%s".', $from->value, $to->value)
        );
    }
}
