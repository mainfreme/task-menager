<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\DataFixtures;

use App\Domain\Model\User\User;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = User::create(
            name: 'admin',
            username: Username::fromString('admin'),
            email: Email::fromString('admin@test.pl'),
            address: null,
            phone: null,
            website: null,
            company: null,
            passwordHash: password_hash('admin', PASSWORD_BCRYPT),
        );

        $userEntity = UserEntity::fromDomain($user);
        $manager->persist($userEntity);
        $manager->flush();
    }
}
