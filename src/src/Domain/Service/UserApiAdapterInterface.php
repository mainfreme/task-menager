<?php

declare(strict_types=1);

namespace App\Domain\Service;

interface UserApiAdapterInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchUsers(): array;
}
