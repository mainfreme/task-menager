<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Geo;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class GeoTest extends TestCase
{
    #[Test]
    #[DataProvider('validGeoProvider')]
    public function creates_valid_geo_coordinates(string $lat, string $lng): void
    {
        $geo = Geo::fromString($lat, $lng);
        
        $this->assertEquals($lat, $geo->getLat());
        $this->assertEquals($lng, $geo->getLng());
        $this->assertEquals("$lat, $lng", $geo->toString());
    }

    public static function validGeoProvider(): array
    {
        return [
            'zero coordinates' => ['0', '0'],
            'positive coordinates' => ['52.2297', '21.0122'],
            'negative coordinates' => ['-33.8688', '-151.2093'],
            'boundary north pole' => ['90', '0'],
            'boundary south pole' => ['-90', '0'],
            'boundary east' => ['0', '180'],
            'boundary west' => ['0', '-180'],
            'decimal precision' => ['52.22970000', '21.01220000'],
        ];
    }

    #[Test]
    #[DataProvider('invalidGeoProvider')]
    public function rejects_invalid_geo_coordinates(string $lat, string $lng, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        
        Geo::fromString($lat, $lng);
    }

    public static function invalidGeoProvider(): array
    {
        return [
            'empty lat' => ['', '21.0122', 'Szerokość geograficzna (lat) nie może być pusta'],
            'empty lng' => ['52.2297', '', 'Długość geograficzna (lng) nie może być pusta'],
            'lat too high' => ['91', '0', 'Szerokość geograficzna (lat) musi być między -90 a 90'],
            'lat too low' => ['-91', '0', 'Szerokość geograficzna (lat) musi być między -90 a 90'],
            'lng too high' => ['0', '181', 'Długość geograficzna (lng) musi być między -180 a 180'],
            'lng too low' => ['0', '-181', 'Długość geograficzna (lng) musi być między -180 a 180'],
            'lat out of range high' => ['100', '0', 'Szerokość geograficzna (lat) musi być między -90 a 90'],
            'lng out of range low' => ['0', '-200', 'Długość geograficzna (lng) musi być między -180 a 180'],
        ];
    }

    #[Test]
    public function converts_to_array(): void
    {
        $geo = Geo::fromString('52.2297', '21.0122');
        
        $this->assertEquals([
            'lat' => '52.2297',
            'lng' => '21.0122',
        ], $geo->toArray());
    }

    #[Test]
    public function two_geo_with_same_coordinates_are_equal(): void
    {
        $geo1 = Geo::fromString('52.2297', '21.0122');
        $geo2 = Geo::fromString('52.2297', '21.0122');
        
        $this->assertTrue($geo1->equals($geo2));
    }

    #[Test]
    public function two_geo_with_different_coordinates_are_not_equal(): void
    {
        $geo1 = Geo::fromString('52.2297', '21.0122');
        $geo2 = Geo::fromString('51.5074', '-0.1278');
        
        $this->assertFalse($geo1->equals($geo2));
    }
}
