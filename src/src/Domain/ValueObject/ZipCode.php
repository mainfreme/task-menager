<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class ZipCode extends StringValueObject
{
    #[ORM\Column(name: "zipcode", type: "string", length: 10)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Zip code nie może być pusty');
        Assert::length($value, 10, 'Zip code musi mieć dokładnie %2$d znaków, podano %d');
        Assert::numeric($value, 'Zip code musi być liczbą');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}