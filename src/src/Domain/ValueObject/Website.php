<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;


#[ORM\Embeddable]
final class Website extends StringValueObject
{
    #[ORM\Column(name: "website", type: "string", length: 255)]
    protected string $value;

    protected function validate(string $value): void
    {
        Assert::notEmpty($value, 'Strona internetowa nie może być pusta');
        
        $isValidUrl = filter_var($value, FILTER_VALIDATE_URL) !== false;
        Assert::true($isValidUrl, sprintf('Nieprawidłowy URL: %s', $value));
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}