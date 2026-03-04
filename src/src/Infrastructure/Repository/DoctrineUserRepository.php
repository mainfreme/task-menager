<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Model\User\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;
use Doctrine\ORM\EntityManager;

final class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManager $entityManager,
    ) {
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(User::class)->findAll();
    }

    public function findById(int $id): ?User
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    public function findByUsername(Username $username): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username->getValue()]);
    }

    public function findByEmail(Email $email): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email->getValue()]);
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
    }

    public function existUserByEmail(Email $email): bool
    {
        return $this->entityManager->getRepository(User::class)->count(['email' => $email->getValue()]) > 0;
    }

    public function flush(): void
    {
        $this->entityManager->flush();
    }
}