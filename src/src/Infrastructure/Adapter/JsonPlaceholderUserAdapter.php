<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Domain\Service\UserApiAdapterInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class JsonPlaceholderUserAdapter implements UserApiAdapterInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiUrl,
    ) {
    }

    public function fetchUsers(): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl, [
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();

            if (200 !== $statusCode) {
                throw new \RuntimeException(
                    sprintf('API returned status code %d', $statusCode)
                );
            }

            return $response->toArray();
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException(
                sprintf('Transport error while fetching users: %s', $e->getMessage()),
                0,
                $e
            );
        } catch (ClientExceptionInterface|ServerExceptionInterface $e) {
            throw new \RuntimeException(
                sprintf('HTTP error while fetching users: %s', $e->getMessage()),
                0,
                $e
            );
        } catch (DecodingExceptionInterface $e) {
            throw new \RuntimeException(
                'API response is not an array',
                0,
                $e
            );
        }
    }
}
