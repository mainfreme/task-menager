<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Company;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CompanyTest extends TestCase
{
    #[Test]
    public function creates_valid_company(): void
    {
        $company = Company::fromString('Tech Corp', 'Innovation first', 'synergize solutions');
        
        $this->assertEquals('Tech Corp', $company->getName());
        $this->assertEquals('Innovation first', $company->getCatchPhrase());
        $this->assertEquals('synergize solutions', $company->getBs());
        $this->assertEquals('Tech Corp - Innovation first', $company->toString());
    }

    #[Test]
    public function creates_company_from_array(): void
    {
        $data = [
            'name' => 'Tech Corp',
            'catchPhrase' => 'Innovation first',
            'bs' => 'synergize solutions',
        ];
        
        $company = Company::fromArray($data);
        
        $this->assertEquals('Tech Corp', $company->getName());
        $this->assertEquals('Innovation first', $company->getCatchPhrase());
        $this->assertEquals('synergize solutions', $company->getBs());
    }

    #[Test]
    #[DataProvider('invalidCompanyProvider')]
    public function rejects_invalid_company_data(string $name, string $catchPhrase, string $bs, string $expectedMessage): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($expectedMessage);
        
        Company::fromString($name, $catchPhrase, $bs);
    }

    public static function invalidCompanyProvider(): array
    {
        return [
            'empty name' => ['', 'Innovation first', 'synergize', 'Nazwa firmy nie może być pusta'],
            'empty catchPhrase' => ['Tech Corp', '', 'synergize', 'Slogan firmy nie może być pusty'],
            'empty bs' => ['Tech Corp', 'Innovation first', '', 'BS firmy nie może być pusty'],
        ];
    }

    #[Test]
    public function converts_to_array(): void
    {
        $company = Company::fromString('Tech Corp', 'Innovation first', 'synergize solutions');
        
        $this->assertEquals([
            'name' => 'Tech Corp',
            'catchPhrase' => 'Innovation first',
            'bs' => 'synergize solutions',
        ], $company->toArray());
    }

    #[Test]
    public function two_companies_with_same_data_are_equal(): void
    {
        $company1 = Company::fromString('Tech Corp', 'Innovation first', 'synergize');
        $company2 = Company::fromString('Tech Corp', 'Innovation first', 'synergize');
        
        $this->assertTrue($company1->equals($company2));
    }

    #[Test]
    public function two_companies_with_different_data_are_not_equal(): void
    {
        $company1 = Company::fromString('Tech Corp', 'Innovation first', 'synergize');
        $company2 = Company::fromString('Other Corp', 'Innovation first', 'synergize');
        
        $this->assertFalse($company1->equals($company2));
    }
}
