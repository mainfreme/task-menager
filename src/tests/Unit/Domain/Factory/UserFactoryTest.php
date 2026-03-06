<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Factory;

use App\Domain\Exception\UserCreationException;
use App\Domain\Factory\UserFactory;
use App\Domain\Model\User\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserFactoryTest extends TestCase
{
    private UserFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new UserFactory();
    }

    #[Test]
    public function createsUserFromValidApiData(): void
    {
        $apiData = $this->getValidApiData();

        $user = $this->factory->createFromApiData($apiData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Leanne Graham', $user->getName());
        $this->assertEquals('Bret', $user->getUsername()->getValue());
        $this->assertEquals('Sincere@april.biz', $user->getEmail()->getValue());
    }

    #[Test]
    public function throwsExceptionWhenEmailIsMissing(): void
    {
        $this->expectException(UserCreationException::class);
        $this->expectExceptionMessage('Required field "email" is missing');

        $apiData = $this->getValidApiData();
        unset($apiData['email']);

        $this->factory->createFromApiData($apiData);
    }

    #[Test]
    public function throwsExceptionWhenEmailIsInvalid(): void
    {
        $this->expectException(UserCreationException::class);

        $apiData = $this->getValidApiData();
        $apiData['email'] = 'invalid-email';

        $this->factory->createFromApiData($apiData);
    }

    #[Test]
    public function returnsNullAddressWhenAddressIsIncomplete(): void
    {
        $apiData = $this->getValidApiData();
        unset($apiData['address']['street']);

        $user = $this->factory->createFromApiData($apiData);

        $this->assertNull($user->getAddress());
    }

    #[Test]
    public function returnsNullCompanyWhenCompanyIsIncomplete(): void
    {
        $apiData = $this->getValidApiData();
        unset($apiData['company']['name']);

        $user = $this->factory->createFromApiData($apiData);

        $this->assertNull($user->getCompany());
    }

    #[Test]
    public function returnsNullAddressWhenGeoDataIsIncomplete(): void
    {
        $apiData = $this->getValidApiData();
        unset($apiData['address']['geo']['lat']);

        $user = $this->factory->createFromApiData($apiData);

        $this->assertNull($user->getAddress());
    }

    private function getValidApiData(): array
    {
        return [
            'id' => 1,
            'name' => 'Leanne Graham',
            'username' => 'Bret',
            'email' => 'Sincere@april.biz',
            'address' => [
                'street' => 'Kulas Light',
                'suite' => 'Apt. 556',
                'city' => 'Gwenborough',
                'zipcode' => '92998-3874',
                'geo' => [
                    'lat' => '-37.3159',
                    'lng' => '81.1496',
                ],
            ],
            'phone' => '1-770-736-8031',
            'website' => 'hildegard.org',
            'company' => [
                'name' => 'Romaguera-Crona',
                'catchPhrase' => 'Multi-layered client-server neural-net',
                'bs' => 'harness real-time e-markets',
            ],
        ];
    }
}
