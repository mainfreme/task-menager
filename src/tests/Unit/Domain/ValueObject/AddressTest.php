<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Geo;
use App\Domain\ValueObject\ZipCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    #[Test]
    public function createsValidAddress(): void
    {
        $address = new Address(
            'Main Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $this->assertEquals('Main Street', $address->getStreet());
        $this->assertEquals('Apt. 123', $address->getSuite());
        $this->assertEquals('New York', $address->getCity());
        $this->assertEquals('1234567890', $address->getZipCode()->getValue());
    }

    #[Test]
    public function createsAddressFromArray(): void
    {
        $data = [
            'street' => 'Main Street',
            'suite' => 'Apt. 123',
            'city' => 'New York',
            'zipcode' => '1234567890',
            'geo' => [
                'lat' => '40.7128',
                'lng' => '-74.0060',
            ],
        ];

        $address = Address::fromArray($data);

        $this->assertEquals('Main Street', $address->getStreet());
        $this->assertEquals('Apt. 123', $address->getSuite());
        $this->assertEquals('New York', $address->getCity());
    }

    #[Test]
    #[DataProvider('invalidAddressProvider')]
    public function rejectsInvalidAddressData(string $street, string $suite, string $city, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        new Address(
            $street,
            $suite,
            $city,
            ZipCode::fromString('1234567890'),
            Geo::fromString('0', '0')
        );
    }

    public static function invalidAddressProvider(): array
    {
        return [
            'empty street' => ['', 'Apt. 123', 'New York', 'Ulica nie może być pusta'],
            'empty suite' => ['Main Street', '', 'New York', 'Numer lokalu nie może być pusty'],
            'empty city' => ['Main Street', 'Apt. 123', '', 'Miasto nie może być puste'],
        ];
    }

    #[Test]
    public function formatsFullAddress(): void
    {
        $address = new Address(
            'Main Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $this->assertEquals('Main Street Apt. 123, New York 1234567890', $address->getFullAddress());
    }

    #[Test]
    public function convertsToArray(): void
    {
        $address = new Address(
            'Main Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $array = $address->toArray();

        $this->assertEquals('Main Street', $array['street']);
        $this->assertEquals('Apt. 123', $array['suite']);
        $this->assertEquals('New York', $array['city']);
        $this->assertEquals('1234567890', $array['zipcode']);
        $this->assertIsArray($array['geo']);
        $this->assertEquals('40.7128', $array['geo']['lat']);
        $this->assertEquals('-74.0060', $array['geo']['lng']);
    }

    #[Test]
    public function twoAddressesWithSameDataAreEqual(): void
    {
        $address1 = new Address(
            'Main Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $address2 = new Address(
            'Main Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $this->assertTrue($address1->equals($address2));
    }

    #[Test]
    public function twoAddressesWithDifferentStreetsAreNotEqual(): void
    {
        $address1 = new Address(
            'Main Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $address2 = new Address(
            'Second Street',
            'Apt. 123',
            'New York',
            ZipCode::fromString('1234567890'),
            Geo::fromString('40.7128', '-74.0060')
        );

        $this->assertFalse($address1->equals($address2));
    }

    #[Test]
    public function failsWhenNestedZipcodeIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Zip code musi mieć dokładnie 10 znaków');

        Address::fromArray([
            'street' => 'Main Street',
            'suite' => 'Apt. 123',
            'city' => 'New York',
            'zipcode' => '123',
            'geo' => ['lat' => '0', 'lng' => '0'],
        ]);
    }

    #[Test]
    public function failsWhenNestedGeoIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Szerokość geograficzna (lat) musi być między -90 a 90');

        Address::fromArray([
            'street' => 'Main Street',
            'suite' => 'Apt. 123',
            'city' => 'New York',
            'zipcode' => '1234567890',
            'geo' => ['lat' => '100', 'lng' => '0'],
        ]);
    }
}
