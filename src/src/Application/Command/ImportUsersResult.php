<?php

declare(strict_types=1);

namespace App\Application\Command;

final class ImportUsersResult
{
    public function __construct(
        public readonly int $imported,
        public readonly int $skipped,
        public readonly int $failed,
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
