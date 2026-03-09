<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class UpdateTaskDto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly ?int $assignedUserId = null,
    ) {
    }
}
