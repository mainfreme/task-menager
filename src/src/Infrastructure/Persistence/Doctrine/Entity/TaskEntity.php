<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\TaskAggregate;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tasks')]
class TaskEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, enumType: TaskStatusEnum::class)]
    private TaskStatusEnum $status;

    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(name: 'assigned_user_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private UserEntity $assignedUser;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    private function __construct(
        string $name,
        ?string $description,
        TaskStatusEnum $status,
        UserEntity $assignedUser,
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->id = null;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->assignedUser = $assignedUser;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public static function fromDomain(TaskAggregate $task, UserEntity $assignedUser): self
    {
        return new self(
            name: $task->getName(),
            description: $task->getDescription(),
            status: $task->getStatus(),
            assignedUser: $assignedUser,
            createdAt: $task->getCreatedAt(),
            updatedAt: $task->getUpdatedAt(),
        );
    }

    public function updateFromDomain(TaskAggregate $task): void
    {
        $this->name = $task->getName();
        $this->description = $task->getDescription();
        $this->status = $task->getStatus();
        $this->updatedAt = $task->getUpdatedAt();
    }

    public function toDomain(): TaskAggregate
    {
        if (null === $this->id) {
            throw new \LogicException('Cannot map TaskEntity without id to domain.');
        }

        return TaskAggregate::reconstitute(
            id: $this->id,
            name: $this->name,
            description: $this->description,
            status: $this->status,
            assignedUserId: $this->assignedUser->getId()
                ?? throw new \LogicException('Cannot map TaskEntity with unresolved assignedUser to domain.'),
            createdAt: $this->createdAt ?? new \DateTimeImmutable(),
            updatedAt: $this->updatedAt ?? new \DateTimeImmutable(),
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): TaskStatusEnum
    {
        return $this->status;
    }

    public function getAssignedUser(): UserEntity
    {
        return $this->assignedUser;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
