<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Email extends StringValueObject
{
    #[ORM\Column(name: "email", type: "string", length: 180)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Email nie może być pusty');
        Assert::email($value, 'Email nie jest prawidłowy');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}