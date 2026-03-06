<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Phone extends StringValueObject
{
    #[ORM\Column(name: 'phone', type: 'string', length: 15, nullable: true)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::regex($value, '/^(?=(?:\D*\d){1,15}$)(?:\+?\s?(?:\(\d+\)|\d+))?(?:[ -]?\d+)*$/', 'Numer telefonu musi być w poprawnym formacie "%s"');

        Assert::maxLength($value, 15, 'Numer telefonu nie może być dłuższy niż %2$d znaków');
    }

    public static function fromString(string $phone): self
    {
        return new self($phone);
    }
}
