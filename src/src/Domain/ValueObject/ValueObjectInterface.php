<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

interface ValueObjectInterface
{
    public function equals(self $other): bool;
    
    public function toString(): string;
}
