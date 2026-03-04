<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Username extends StringValueObject
{
    #[ORM\Column(name: "username", type: "string", length: 100)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Nazwa użytkownika nie może być pusta');
        Assert::minLength($value, 3, 'Nazwa użytkownika musi mieć co najmniej %2$d znaki, podano %d');
    }

    public static function fromString(string $username): self
    {
        return new self($username);
    }
}