<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Phone extends StringValueObject
{
    #[ORM\Column(name: "phone", type: "string", length: 20)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Telefon nie może być pusty');
        Assert::regex($value, '/^\+[1-9]\d{1,14}$/', 'Telefon musi być w poprawnym formacie');
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}