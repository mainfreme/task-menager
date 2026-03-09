<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\Task\Event\TaskEventType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'task_events')]
class TaskEventEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TaskEntity::class)]
    #[ORM\JoinColumn(name: 'task_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private TaskEntity $task;

    #[ORM\Column(name: 'event_type', type: 'string', length: 50)]
    private string $eventType;

    /** @var array<string, mixed> */
    #[ORM\Column(type: 'json')]
    private array $payload;

    #[ORM\ManyToOne(targetEntity: UserEntity::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private UserEntity $user;

    #[ORM\Column(name: 'occurred_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $occurredAt;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        TaskEntity $task,
        TaskEventType $eventType,
        array $payload,
        UserEntity $user,
        \DateTimeImmutable $occurredAt,
    ) {
        $this->task = $task;
        $this->eventType = $eventType->value;
        $this->payload = $payload;
        $this->user = $user;
        $this->occurredAt = $occurredAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskId(): int
    {
        return $this->task->getId() ?? throw new \LogicException('Task must have id.');
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getUserId(): int
    {
        return $this->user->getId() ?? throw new \LogicException('User must have id.');
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
