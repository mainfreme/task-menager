<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class TaskPersistenceException extends \RuntimeException
{
    public static function onSave(\Throwable $previous): self
    {
        return new self(
            sprintf('Failed to save task: %s', $previous->getMessage()),
            0,
            $previous
        );
    }

    public static function onDelete(int $id, \Throwable $previous): self
    {
        return new self(
            sprintf('Failed to delete task with id "%d": %s', $id, $previous->getMessage()),
            0,
            $previous
        );
    }

    public static function onFlush(\Throwable $previous): self
    {
        return new self(
            sprintf('Failed to flush changes: %s', $previous->getMessage()),
            0,
            $previous
        );
    }
}
