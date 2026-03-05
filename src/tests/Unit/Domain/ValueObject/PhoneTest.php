<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Phone;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhoneTest extends TestCase
{
    #[Test]
    #[DataProvider('validPhoneProvider')]
    public function creates_valid_phone(string $validPhone): void
    {
        $phone = Phone::fromString($validPhone);
        
        $this->assertEquals($validPhone, $phone->getValue());
    }

    public static function validPhoneProvider(): array
    {
        return [
            'US format' => ['+12345678901'],
            'Poland format' => ['+48123456789'],
            'minimum length' => ['+111'],
            'maximum length' => ['+123456789012345'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPhoneProvider')]
    public function rejects_invalid_phones(string $invalidPhone, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        
        Phone::fromString($invalidPhone);
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            'empty string' => ['', 'Telefon nie może być pusty'],
            'no plus sign' => ['48123456789', 'Telefon musi być w poprawnym formacie'],
            'starts with zero after plus' => ['+0123456789', 'Telefon musi być w poprawnym formacie'],
            'contains letters' => ['+48abc123456', 'Telefon musi być w poprawnym formacie'],
            'contains spaces' => ['+48 123 456 789', 'Telefon musi być w poprawnym formacie'],
            'contains dashes' => ['+48-123-456-789', 'Telefon musi być w poprawnym formacie'],
            'too long' => ['+1234567890123456', 'Telefon musi być w poprawnym formacie'],
            'only plus sign' => ['+', 'Telefon musi być w poprawnym formacie'],
            'plus sign in wrong place' => ['123+456', 'Telefon musi być w poprawnym formacie'],
        ];
    }

    #[Test]
    public function two_phones_with_same_value_are_equal(): void
    {
        $phone1 = Phone::fromString('+48123456789');
        $phone2 = Phone::fromString('+48123456789');
        
        $this->assertTrue($phone1->equals($phone2));
    }

    #[Test]
    public function two_phones_with_different_values_are_not_equal(): void
    {
        $phone1 = Phone::fromString('+48123456789');
        $phone2 = Phone::fromString('+48987654321');
        
        $this->assertFalse($phone1->equals($phone2));
    }
}
