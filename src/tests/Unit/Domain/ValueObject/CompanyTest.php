<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Company;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CompanyTest extends TestCase
{
    #[Test]
    public function createsValidCompany(): void
    {
        $company = Company::fromString('Tech Corp', 'Innovation first', 'synergize solutions');

        $this->assertEquals('Tech Corp', $company->getName());
        $this->assertEquals('Innovation first', $company->getCatchPhrase());
        $this->assertEquals('synergize solutions', $company->getBs());
        $this->assertEquals('Tech Corp - Innovation first', $company->toString());
    }

    #[Test]
    public function createsCompanyFromArray(): void
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
    public function rejectsInvalidCompanyData(string $name, string $catchPhrase, string $bs, string $expectedMessage): void
    {
        $this->expectException(\InvalidArgumentException::class);
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
    public function convertsToArray(): void
    {
        $company = Company::fromString('Tech Corp', 'Innovation first', 'synergize solutions');

        $this->assertEquals([
            'name' => 'Tech Corp',
            'catchPhrase' => 'Innovation first',
            'bs' => 'synergize solutions',
        ], $company->toArray());
    }

    #[Test]
    public function twoCompaniesWithSameDataAreEqual(): void
    {
        $company1 = Company::fromString('Tech Corp', 'Innovation first', 'synergize');
        $company2 = Company::fromString('Tech Corp', 'Innovation first', 'synergize');

        $this->assertTrue($company1->equals($company2));
    }

    #[Test]
    public function twoCompaniesWithDifferentDataAreNotEqual(): void
    {
        $company1 = Company::fromString('Tech Corp', 'Innovation first', 'synergize');
        $company2 = Company::fromString('Other Corp', 'Innovation first', 'synergize');

        $this->assertFalse($company1->equals($company2));
    }
}
