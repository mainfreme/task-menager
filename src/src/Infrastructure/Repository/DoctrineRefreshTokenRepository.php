<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Repository\RefreshTokenRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\RefreshTokenEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineRefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function findByToken(string $token): ?RefreshTokenEntity
    {
        return $this->entityManager
            ->getRepository(RefreshTokenEntity::class)
            ->findOneBy(['token' => $token]);
    }

    public function save(RefreshTokenEntity $refreshToken): void
    {
        $this->entityManager->persist($refreshToken);
    }

    public function revokeAllForUser(int $userId): void
    {
        $this->entityManager->createQueryBuilder()
            ->update(RefreshTokenEntity::class, 'rt')
            ->set('rt.revoked', ':revoked')
            ->where('rt.user = :userId')
            ->andWhere('rt.revoked = :notRevoked')
            ->setParameter('revoked', true)
            ->setParameter('userId', $userId)
            ->setParameter('notRevoked', false)
            ->getQuery()
            ->execute();
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
