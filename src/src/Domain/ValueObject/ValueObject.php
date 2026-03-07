<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

abstract class ValueObject implements ValueObjectInterface
{
    public function equals(ValueObjectInterface|self $other): bool
    {
        if (!$other instanceof static) {
            return false;
        }

        return $this->toComparable() === $other->toComparable();
    }

    abstract public function toString(): string;

    /**
     * @return string|array<string, mixed>
     */
    abstract protected function toComparable(): string|array;

    public function __toString(): string
    {
        return $this->toString();
    }
}
