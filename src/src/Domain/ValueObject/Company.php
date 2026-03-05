<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Company extends ValueObject
{

    public function __construct(
        #[ORM\Column(name: 'company_name', type: 'string', length: 255, nullable: true)]
        private string $name,
        #[ORM\Column(name: 'company_catch_phrase', type: 'string', length: 500, nullable: true)]
        private string $catchPhrase,
        #[ORM\Column(name: 'company_bs', type: 'string', length: 500, nullable: true)]
        private string $bs
    ) {
        Assert::notEmpty($name, 'Nazwa firmy nie może być pusta');
        Assert::notEmpty($catchPhrase, 'Slogan firmy nie może być pusty');
        Assert::notEmpty($bs, 'BS firmy nie może być pusty');
    }

    public static function fromString(string $name, string $catchPhrase, string $bs): self
    {
        return new self($name, $catchPhrase, $bs);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            catchPhrase: $data['catchPhrase'],
            bs: $data['bs'],
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCatchPhrase(): string
    {
        return $this->catchPhrase;
    }

    public function getBs(): string
    {
        return $this->bs;
    }

    public function toString(): string
    {
        return sprintf('%s - %s', $this->name, $this->catchPhrase);
    }

    protected function toComparable(): string
    {
        return json_encode([
            'name' => $this->name,
            'catchPhrase' => $this->catchPhrase,
            'bs' => $this->bs,
        ]);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'catchPhrase' => $this->catchPhrase,
            'bs' => $this->bs,
        ];
    }
}
