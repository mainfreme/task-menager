<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\User\User;
use App\Domain\ValueObject\Address;
use App\Domain\ValueObject\Company;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Phone;
use App\Domain\ValueObject\Username;
use App\Domain\ValueObject\Website;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Embedded(class: Username::class, columnPrefix: false)]
    private Username $username;

    #[ORM\Embedded(class: Email::class, columnPrefix: false)]
    private Email $email;

    #[ORM\Embedded(class: Address::class, columnPrefix: 'address_')]
    private ?Address $address;

    #[ORM\Embedded(class: Phone::class, columnPrefix: false)]
    private ?Phone $phone;

    #[ORM\Embedded(class: Website::class, columnPrefix: false)]
    private ?Website $website;

    #[ORM\Embedded(class: Company::class, columnPrefix: false)]
    private ?Company $company;

    #[ORM\Column(name: 'password', type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    private function __construct(
        string $name,
        Username $username,
        Email $email,
        ?Address $address,
        ?Phone $phone,
        ?Website $website,
        ?Company $company,
        string $password,
        ?\DateTimeImmutable $createdAt,
    ) {
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->address = $address;
        $this->phone = $phone;
        $this->website = $website;
        $this->company = $company;
        $this->password = $password;
        $this->createdAt = $createdAt;
    }

    public static function fromDomain(User $user): self
    {
        return new self(
            name: $user->getName(),
            username: $user->getUsername(),
            email: $user->getEmail(),
            address: $user->getAddress(),
            phone: $user->getPhone(),
            website: $user->getWebsite(),
            company: $user->getCompany(),
            password: $user->getPasswordHash(),
            createdAt: $user->getCreatedAt(),
        );
    }

    public function toDomain(): User
    {
        if (null === $this->id) {
            throw new \LogicException('Cannot map UserEntity without id to domain.');
        }

        return User::reconstitute(
            id: $this->id,
            name: $this->name,
            username: $this->username,
            email: $this->email,
            address: $this->address,
            phone: $this->phone,
            website: $this->website,
            company: $this->company,
            passwordHash: $this->password,
            createdAt: $this->createdAt,
        );
    }
}
