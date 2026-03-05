<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Website;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WebsiteTest extends TestCase
{
    #[Test]
    #[DataProvider('validWebsiteProvider')]
    public function createsValidWebsite(string $validUrl): void
    {
        $website = Website::fromString($validUrl);

        $this->assertEquals($validUrl, $website->getValue());
    }

    public static function validWebsiteProvider(): array
    {
        return [
            'http url' => ['http://example.com'],
            'https url' => ['https://example.com'],
            'with www' => ['https://www.example.com'],
            'with path' => ['https://example.com/path/to/page'],
            'with query params' => ['https://example.com?param=value'],
            'with port' => ['https://example.com:8080'],
            'subdomain' => ['https://subdomain.example.com'],
        ];
    }

    #[Test]
    #[DataProvider('invalidWebsiteProvider')]
    public function rejectsInvalidWebsites(string $invalidUrl, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);

        Website::fromString($invalidUrl);
    }

    public static function invalidWebsiteProvider(): array
    {
        return [
            'empty string' => ['', 'Strona internetowa nie może być pusta'],
            'no protocol' => ['example.com', 'Nieprawidłowy URL: example.com'],
            'spaces in url' => ['https://exam ple.com', 'Nieprawidłowy URL: https://exam ple.com'],
            'only protocol' => ['https://', 'Nieprawidłowy URL: https://'],
            'just text' => ['not a url', 'Nieprawidłowy URL: not a url'],
        ];
    }

    #[Test]
    public function twoWebsitesWithSameValueAreEqual(): void
    {
        $website1 = Website::fromString('https://example.com');
        $website2 = Website::fromString('https://example.com');

        $this->assertTrue($website1->equals($website2));
    }

    #[Test]
    public function twoWebsitesWithDifferentValuesAreNotEqual(): void
    {
        $website1 = Website::fromString('https://example.com');
        $website2 = Website::fromString('https://other.com');

        $this->assertFalse($website1->equals($website2));
    }
}
