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
            'url' => ['example.com'],
            'https url' => ['https://example.com'],
            'with www' => ['www.example.com'],
            'with path' => ['example.com/path/to/page'],
            'with query params' => ['example.com?param=value'],
            'with port' => ['example.com:8080'],
            'subdomain' => ['subdomain.example.com'],
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
            'empty string' => ['', 'Format [] nie jest poprawnym adresem URL: '],
            'spaces in url' => ['https://exam ple.com', 'Format [https://exam ple.com] nie jest poprawnym adresem URL: https://exam ple.com'],
            'only protocol' => ['https://', 'Format [https://] nie jest poprawnym adresem URL: https://'],
            'just text' => ['not a url', 'Format [not a url] nie jest poprawnym adresem URL: not a url'],
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
