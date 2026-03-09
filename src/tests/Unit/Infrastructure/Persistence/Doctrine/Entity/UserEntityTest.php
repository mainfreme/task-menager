<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\User\UserAggregate;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserEntityTest extends TestCase
{
    #[Test]
    #[DataProvider('adminUsernameProvider')]
    public function adminUserHasRoleAdmin(string $username): void
    {
        $user = $this->createUserEntity($username, 'admin@test.pl');

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertCount(2, $roles);
    }

    #[Test]
    #[DataProvider('regularUsernameProvider')]
    public function regularUserHasOnlyRoleUser(string $username): void
    {
        $user = $this->createUserEntity($username, 'user@test.pl');

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertNotContains('ROLE_ADMIN', $roles);
        $this->assertCount(1, $roles);
    }

    #[Test]
    public function usernameAdminIsCaseSensitive(): void
    {
        $adminUser = $this->createUserEntity('admin', 'admin@test.pl');
        $adminUpperCaseUser = $this->createUserEntity('Admin', 'admin2@test.pl');

        $this->assertContains('ROLE_ADMIN', $adminUser->getRoles());
        $this->assertNotContains('ROLE_ADMIN', $adminUpperCaseUser->getRoles());
    }

    #[Test]
    public function usernameAdminAsPartOfStringDoesNotGrantAdmin(): void
    {
        $user = $this->createUserEntity('adminuser', 'user@test.pl');

        $roles = $user->getRoles();

        $this->assertNotContains('ROLE_ADMIN', $roles);
        $this->assertCount(1, $roles);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function adminUsernameProvider(): array
    {
        return [
            'exact admin' => ['admin'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function regularUsernameProvider(): array
    {
        return [
            'regular user' => ['johndoe'],
            'imported user' => ['Bret'],
            'another user' => ['Antonette'],
            'empty-like' => ['user'],
        ];
    }

    private function createUserEntity(string $username, string $email): UserEntity
    {
        $userAggregate = UserAggregate::create(
            name: 'Test User',
            username: Username::fromString($username),
            email: Email::fromString($email),
            address: null,
            phone: null,
            website: null,
            company: null,
            passwordHash: 'hashed',
        );

        return UserEntity::fromDomain($userAggregate);
    }
}
