<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

#[ORM\Embeddable]
final class Address extends ValueObject
{
    public function __construct(
        #[ORM\Column(name: 'street', type: 'string', length: 255, nullable: true)]
        private string $street,
        #[ORM\Column(name: 'suite', type: 'string', length: 100, nullable: true)]
        private string $suite,
        #[ORM\Column(name: 'city', type: 'string', length: 255, nullable: true)]
        private string $city,
        #[ORM\Embedded(class: ZipCode::class, columnPrefix: false)]
        private ZipCode $zipCode,
        #[ORM\Embedded(class: Geo::class, columnPrefix: 'geo_')]
        private Geo $geo,
    ) {
        Assert::notEmpty($street, 'Ulica nie może być pusta');
        Assert::notEmpty($suite, 'Numer lokalu nie może być pusty');
        Assert::notEmpty($city, 'Miasto nie może być puste');
    }

    /**
     * @param array{street: string, suite: string, city: string, zipcode: string, geo: array{lat: string, lng: string}} $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'],
            suite: $data['suite'],
            city: $data['city'],
            zipCode: ZipCode::fromString($data['zipcode']),
            geo: Geo::fromString($data['geo']['lat'], $data['geo']['lng']),
        );
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getSuite(): string
    {
        return $this->suite;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getZipCode(): ZipCode
    {
        return $this->zipCode;
    }

    public function getGeo(): Geo
    {
        return $this->geo;
    }

    public function getFullAddress(): string
    {
        return sprintf(
            '%s %s, %s %s',
            $this->street,
            $this->suite,
            $this->city,
            $this->zipCode->getValue()
        );
    }

    public function toString(): string
    {
        return $this->getFullAddress();
    }

    protected function toComparable(): string
    {
        return json_encode([
            'street' => $this->street,
            'suite' => $this->suite,
            'city' => $this->city,
            'zipCode' => $this->zipCode->getValue(),
            'geo' => $this->geo->toArray(),
        ]);
    }

    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'suite' => $this->suite,
            'city' => $this->city,
            'zipcode' => $this->zipCode->getValue(),
            'geo' => $this->geo->toArray(),
        ];
    }
}
