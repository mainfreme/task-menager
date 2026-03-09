<?php

declare(strict_types=1);

namespace App\Domain\Model\Task\Event;

enum TaskEventType: string
{
    case Created = 'task.created';
    case Updated = 'task.updated';
    case StatusChanged = 'task.status_changed';
    case Viewed = 'task.viewed';
}
