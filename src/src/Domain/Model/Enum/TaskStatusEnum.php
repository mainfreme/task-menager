<?php

declare(strict_types=1);

namespace App\Domain\Model\Enum;

enum TaskStatusEnum: string
{
    case ToDo = 'To Do';
    case InProgress = 'In Progress';
    case Done = 'Done';
}
