<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Adapter;

use App\Infrastructure\Adapter\JsonPlaceholderUserAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class JsonPlaceholderUserAdapterTest extends TestCase
{
    private const TEST_API_URL = 'https://jsonplaceholder.typicode.com/users';

    #[Test]
    public function fetchesUsersSuccessfully(): void
    {
        $expectedData = [
            [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
            ],
        ];

        $mockResponse = new MockResponse(
            json_encode($expectedData),
            ['http_code' => 200]
        );

        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, self::TEST_API_URL);

        $result = $adapter->fetchUsers();

        $this->assertEquals($expectedData, $result);
    }

    #[Test]
    public function throwsExceptionOnNon200StatusCode(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 404]);
        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, self::TEST_API_URL);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API returned status code 404');

        $adapter->fetchUsers();
    }

    #[Test]
    public function throwsExceptionOnServerError(): void
    {
        $mockResponse = new MockResponse('', ['http_code' => 500]);
        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, self::TEST_API_URL);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API returned status code 500');

        $adapter->fetchUsers();
    }

    #[Test]
    public function throwsExceptionOnTransportError(): void
    {
        $mockResponse = new MockResponse('', [
            'error' => 'Network error',
        ]);

        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, self::TEST_API_URL);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Transport error while fetching users/');

        $adapter->fetchUsers();
    }

    #[Test]
    public function throwsExceptionWhenResponseIsNotArray(): void
    {
        $mockResponse = new MockResponse(
            '"not an array"',
            ['http_code' => 200]
        );

        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, self::TEST_API_URL);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API response is not an array');

        $adapter->fetchUsers();
    }

    #[Test]
    public function returnsEmptyArrayWhenApiReturnsEmptyArray(): void
    {
        $mockResponse = new MockResponse(
            '[]',
            ['http_code' => 200]
        );

        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, self::TEST_API_URL);

        $result = $adapter->fetchUsers();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function usesProvidedApiUrl(): void
    {
        $customApiUrl = 'https://custom-api.example.com/users';
        $expectedData = [['id' => 1, 'name' => 'Custom User']];

        $mockResponse = new MockResponse(
            json_encode($expectedData),
            ['http_code' => 200]
        );

        $httpClient = new MockHttpClient($mockResponse);
        $adapter = new JsonPlaceholderUserAdapter($httpClient, $customApiUrl);

        $result = $adapter->fetchUsers();

        $this->assertEquals($expectedData, $result);
    }
}
