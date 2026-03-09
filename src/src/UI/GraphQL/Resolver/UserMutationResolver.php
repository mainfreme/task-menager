<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Application\Command\ImportUsersFromApiCommand;
use App\Application\Command\ImportUsersFromApiHandler;
use App\Application\DTO\AuthTokenDto;
use App\Application\Service\AuthenticationService;
use App\Application\Validator\LoginInputValidator;

final class UserMutationResolver
{
    public function __construct(
        private readonly AuthenticationService $authenticationService,
        private readonly LoginInputValidator $loginInputValidator,
        private readonly ImportUsersFromApiHandler $importHandler,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function login(array $input): array
    {
        $this->loginInputValidator->validate($input);

        $authToken = $this->authenticationService->login(
            email: $input['email'],
            password: $input['password'],
        );

        return $this->toGraphQL($authToken);
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function refreshToken(array $input): array
    {
        $authToken = $this->authenticationService->refreshToken(
            refreshToken: $input['refreshToken'],
        );

        return $this->toGraphQL($authToken);
    }

    /**
     * @return array<string, mixed>
     */
    public function syncUsersFromApi(): array
    {
        $result = $this->importHandler->handle(new ImportUsersFromApiCommand());

        return $result->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function toGraphQL(AuthTokenDto $authToken): array
    {
        return [
            'token' => $authToken->token,
            'refreshToken' => $authToken->refreshToken,
            'expiresAt' => $authToken->expiresAt->format('c'),
        ];
    }
}
