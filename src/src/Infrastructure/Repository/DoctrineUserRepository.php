<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Model\User\UserAggregate;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<UserAggregate>
     */
    public function findAll(): array
    {
        $entities = $this->entityManager->getRepository(UserEntity::class)->findAll();

        return array_map(
            static fn (UserEntity $entity): UserAggregate => $entity->toDomain(),
            $entities
        );
    }

    public function findById(int $id): ?UserAggregate
    {
        $entity = $this->entityManager->getRepository(UserEntity::class)->find($id);

        return $entity?->toDomain();
    }

    public function findByUsername(Username $username): ?UserAggregate
    {
        $entity = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(UserEntity::class, 'u')
            ->where('u.username.value = :username')
            ->setParameter('username', $username->getValue())
            ->getQuery()
            ->getOneOrNullResult();

        return $entity?->toDomain();
    }

    public function findByEmail(Email $email): ?UserAggregate
    {
        $entity = $this->entityManager->createQueryBuilder()
            ->select('u')
            ->from(UserEntity::class, 'u')
            ->where('u.email.value = :email')
            ->setParameter('email', $email->getValue())
            ->getQuery()
            ->getOneOrNullResult();

        return $entity?->toDomain();
    }

    public function save(UserAggregate $user): void
    {
        $this->entityManager->persist(UserEntity::fromDomain($user));
    }

    public function existUserByEmail(Email $email): bool
    {
        $count = (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(u.id)')
            ->from(UserEntity::class, 'u')
            ->where('u.email.value = :email')
            ->setParameter('email', $email->getValue())
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}
