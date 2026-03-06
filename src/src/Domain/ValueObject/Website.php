<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Website extends StringValueObject
{
    #[ORM\Column(name: 'website', type: 'string', length: 255, nullable: true)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::maxLength($value, 255, 'Adres strony nie może być dłuższy niż %2$d znaków');

        Assert::string($value, 'Format [%s] musi być ciągiem znaków.');
        Assert::regex(
            $value,
            '/^(https?:\/\/)?([\w\d.-]+)+(:\d+)?(\/.*)?(\?.*)?$/',
            "Format [{$value}] nie jest poprawnym adresem URL: $value"
        );

    }

    public static function fromString(string $website): self
    {
        return new self($website);
    }
}
