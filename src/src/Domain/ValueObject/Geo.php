<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Geo extends ValueObject
{
    public function __construct(
        #[ORM\Column(name: 'lat', type: 'decimal', precision: 10, scale: 8, nullable: true)]
        private string $lat,
        #[ORM\Column(name: 'lng', type: 'decimal', precision: 11, scale: 8, nullable: true)]
        private string $lng,
    ) {
        Assert::notSame($lat, '', 'Szerokość geograficzna (lat) nie może być pusta');
        Assert::notSame($lng, '', 'Długość geograficzna (lng) nie może być pusta');
        Assert::range(
            (float) $lat,
            -90,
            90,
            'Szerokość geograficzna (lat) musi być między %2$s a %3$s. Podano: %s'
        );
        Assert::range(
            (float) $lng,
            -180,
            180,
            'Długość geograficzna (lng) musi być między %2$s a %3$s. Podano: %s'
        );
    }

    public static function fromString(string $lat, string $lng): self
    {
        return new self($lat, $lng);
    }

    public function getLat(): string
    {
        return $this->lat;
    }

    public function getLng(): string
    {
        return $this->lng;
    }

    public function toString(): string
    {
        return sprintf('%s, %s', $this->lat, $this->lng);
    }

    protected function toComparable(): string
    {
        return sprintf('%s:%s', $this->lat, $this->lng);
    }

    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lng' => $this->lng,
        ];
    }
}
