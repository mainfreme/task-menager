<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\ZipCode;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ZipCodeTest extends TestCase
{
    #[Test]
    public function creates_valid_zipcode(): void
    {
        $zipCode = ZipCode::fromString('0123456789');
        
        $this->assertEquals('0123456789', $zipCode->getValue());
    }

    #[Test]
    #[DataProvider('invalidZipCodeProvider')]
    public function rejects_invalid_zipcodes(string $invalidZipCode, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        
        ZipCode::fromString($invalidZipCode);
    }

    public static function invalidZipCodeProvider(): array
    {
        return [
            'empty string' => ['', 'Zip code nie może być pusty'],
            'too short' => ['123', 'Zip code musi mieć dokładnie 10 znaków'],
            'too long' => ['12345678901', 'Zip code musi mieć dokładnie 10 znaków'],
            'contains letters' => ['123456789a', 'Zip code musi być liczbą'],
            'contains spaces' => ['123 456 78', 'Zip code musi być liczbą'],
            'contains dashes' => ['12345-6789', 'Zip code musi być liczbą'],
            'only letters' => ['abcdefghij', 'Zip code musi być liczbą'],
        ];
    }

    #[Test]
    public function two_zipcodes_with_same_value_are_equal(): void
    {
        $zipCode1 = ZipCode::fromString('1234567890');
        $zipCode2 = ZipCode::fromString('1234567890');
        
        $this->assertTrue($zipCode1->equals($zipCode2));
    }

    #[Test]
    public function two_zipcodes_with_different_values_are_not_equal(): void
    {
        $zipCode1 = ZipCode::fromString('1234567890');
        $zipCode2 = ZipCode::fromString('0987654321');
        
        $this->assertFalse($zipCode1->equals($zipCode2));
    }
}
