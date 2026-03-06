<?php

declare(strict_types=1);

namespace App\Application\DTO;

final class ImportUsersResult
{
    /**
     * @param array<string> $errors
     */
    public function __construct(
        public readonly int $imported = 0,
        public readonly int $skipped = 0,
        public readonly int $failed = 0,
        public readonly array $errors = [],
    ) {
    }

    public function toArray(): array
    {
        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
            'errors' => $this->errors,
        ];
    }

    public function isSuccess(): bool
    {
        return 0 === $this->failed;
    }
}
