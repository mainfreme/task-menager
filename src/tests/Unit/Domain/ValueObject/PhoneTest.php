<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Phone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PhoneTest extends TestCase
{
    #[Test]
    #[DataProvider('validPhoneProvider')]
    public function createsValidPhone(string $validPhone): void
    {
        $phone = Phone::fromString($validPhone);

        $this->assertEquals($validPhone, $phone->getValue());
    }

    public static function validPhoneProvider(): array
    {
        return [
            'digits only' => ['48123456789'],
            'with plus' => ['+48123456789'],
            'with spaces' => ['123 456 789'],
            'with dash' => ['123-456-789'],
            'single digit' => ['1'],
            'max length exact' => ['123456789012345'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPhoneProvider')]
    public function rejectsInvalidPhones(string $invalidPhone, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        Phone::fromString($invalidPhone);
    }

    public static function invalidPhoneProvider(): array
    {
        return [
            'empty string' => ['', 'Numer telefonu musi być w poprawnym formacie'],
            'contains letters' => ['+48abc123456', 'Numer telefonu musi być w poprawnym formacie'],
            'plus sign in wrong place' => ['123+456', 'Numer telefonu musi być w poprawnym formacie'],
            'only plus sign' => ['+', 'Numer telefonu musi być w poprawnym formacie'],
            'too long with separators' => ['123 456 789 012 3', 'Numer telefonu nie może być dłuższy niż 15 znaków'],
        ];
    }

    #[Test]
    public function twoPhonesWithSameValueAreEqual(): void
    {
        $phone1 = Phone::fromString('+48123456789');
        $phone2 = Phone::fromString('+48123456789');

        $this->assertTrue($phone1->equals($phone2));
    }

    #[Test]
    public function twoPhonesWithDifferentValuesAreNotEqual(): void
    {
        $phone1 = Phone::fromString('+48123456789');
        $phone2 = Phone::fromString('+48987654321');

        $this->assertFalse($phone1->equals($phone2));
    }
}
