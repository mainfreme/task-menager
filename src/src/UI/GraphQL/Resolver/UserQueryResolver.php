<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Domain\Model\User\UserAggregate;
use App\Domain\Repository\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Symfony\Bundle\SecurityBundle\Security;

final class UserQueryResolver
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function me(): ?array
    {
        $securityUser = $this->security->getUser();

        if (!$securityUser instanceof UserEntity) {
            return null;
        }

        $user = $securityUser->toDomain();

        return $this->toGraphQL($user);
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findAll(): array
    {
        return array_map(
            fn (UserAggregate $user): array => $this->toGraphQL($user),
            $this->userRepository->findAll(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toGraphQL(UserAggregate $user): array
    {
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'username' => $user->getUsername()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'phone' => $user->getPhone()?->getValue(),
            'website' => $user->getWebsite()?->getValue(),
        ];
    }
}
