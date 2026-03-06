<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\ZipCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ZipCodeTest extends TestCase
{
    #[Test]
    public function createsValidZipcode(): void
    {
        $zipCode = ZipCode::fromString('0123456789');

        $this->assertEquals('0123456789', $zipCode->getValue());
    }

    #[Test]
    #[DataProvider('invalidZipCodeProvider')]
    public function rejectsInvalidZipcodes(string $invalidZipCode, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        ZipCode::fromString($invalidZipCode);
    }

    public static function invalidZipCodeProvider(): array
    {
        return [
            'too short' => ['123', 'Zip code musi mieć dokładnie 10 znaków'],
            'too long' => ['12345678901', 'Zip code musi mieć dokładnie 10 znaków'],
        ];
    }

    #[Test]
    public function twoZipcodesWithSameValueAreEqual(): void
    {
        $zipCode1 = ZipCode::fromString('1234567890');
        $zipCode2 = ZipCode::fromString('1234567890');

        $this->assertTrue($zipCode1->equals($zipCode2));
    }

    #[Test]
    public function twoZipcodesWithDifferentValuesAreNotEqual(): void
    {
        $zipCode1 = ZipCode::fromString('1234567890');
        $zipCode2 = ZipCode::fromString('0987654321');

        $this->assertFalse($zipCode1->equals($zipCode2));
    }
}
