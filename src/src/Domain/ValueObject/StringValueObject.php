<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

abstract class StringValueObject extends ValueObject
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->validate($value);
        $this->value = $value;
    }

    abstract protected function validate(string $value): void;

    public function getValue(): string
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }

    protected function toComparable(): string
    {
        return $this->value;
    }
}
