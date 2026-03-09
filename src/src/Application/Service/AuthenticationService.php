<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Application\DTO\AuthTokenDto;
use App\Domain\Exception\InvalidCredentialsException;
use App\Domain\Exception\InvalidRefreshTokenException;
use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Infrastructure\Persistence\Doctrine\Entity\RefreshTokenEntity;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class AuthenticationService
{
    private const REFRESH_TOKEN_TTL_DAYS = 30;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly RefreshTokenRepositoryInterface $refreshTokenRepository,
        private readonly PasswordHasherFactoryInterface $hasherFactory,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function login(string $email, string $password): AuthTokenDto
    {
        $user = $this->userRepository->findByEmail(Email::fromString($email));

        if (null === $user) {
            throw new InvalidCredentialsException();
        }

        $userEntity = $this->entityManager
            ->getRepository(UserEntity::class)
            ->find($user->getId());

        if (null === $userEntity) {
            throw new InvalidCredentialsException();
        }

        $hasher = $this->hasherFactory->getPasswordHasher($userEntity);

        if (!$hasher->verify($userEntity->getPassword(), $password)) {
            throw new InvalidCredentialsException();
        }

        return $this->generateTokenPair($userEntity);
    }

    public function refreshToken(string $refreshToken): AuthTokenDto
    {
        $tokenEntity = $this->refreshTokenRepository->findByToken($refreshToken);

        if (null === $tokenEntity || !$tokenEntity->isValid()) {
            throw new InvalidRefreshTokenException();
        }

        $tokenEntity->revoke();

        $userEntity = $tokenEntity->getUser();

        $authTokenDto = $this->generateTokenPair($userEntity);

        $this->refreshTokenRepository->flush();

        return $authTokenDto;
    }

    private function generateTokenPair(UserEntity $userEntity): AuthTokenDto
    {
        $accessToken = $this->jwtManager->create($userEntity);

        $refreshTokenString = bin2hex(random_bytes(64));
        $expiresAt = new \DateTimeImmutable(sprintf('+%d days', self::REFRESH_TOKEN_TTL_DAYS));

        $refreshTokenEntity = RefreshTokenEntity::create(
            token: $refreshTokenString,
            user: $userEntity,
            expiresAt: $expiresAt,
        );

        $this->refreshTokenRepository->save($refreshTokenEntity);
        $this->refreshTokenRepository->flush();

        return new AuthTokenDto(
            token: $accessToken,
            refreshToken: $refreshTokenString,
            expiresAt: $expiresAt,
        );
    }
}
