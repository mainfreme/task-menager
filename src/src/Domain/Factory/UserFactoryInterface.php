<?php

declare(strict_types=1);

namespace App\Domain\Factory;

use App\Domain\Exception\UserCreationException;
use App\Domain\Model\User\UserAggregate;

interface UserFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     *
     * @throws UserCreationException
     */
    public function createFromApiData(array $data): UserAggregate;
}
