<?php

declare(strict_types=1);

namespace App\Domain\Model\Task;

use App\Application\DTO\UpdateTaskDto;
use App\Domain\Exception\InvalidStatusTransitionException;
use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\Event\TaskCreatedEvent;
use App\Domain\Model\Task\Event\TaskDomainEventInterface;
use App\Domain\Model\Task\Event\TaskStatusChangedEvent;
use App\Domain\Model\Task\Event\TaskUpdatedEvent;
use App\Domain\Model\Task\Strategy\StatusTransitionResolverInterface;

final class TaskAggregate
{
    /** @var TaskDomainEventInterface[] */
    private array $recordedEvents = [];

    private function __construct(
        private ?int $id,
        private string $name,
        private ?string $description,
        private TaskStatusEnum $status,
        private int $assignedUserId,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(
        string $name,
        ?string $description,
        int $assignedUserId,
    ): self {
        $now = new \DateTimeImmutable();

        $aggregate = new self(
            id: null,
            name: $name,
            description: $description,
            status: TaskStatusEnum::ToDo,
            assignedUserId: $assignedUserId,
            createdAt: $now,
            updatedAt: $now,
        );

        $aggregate->recordEvent(new TaskCreatedEvent(
            name: $name,
            description: $description,
            status: TaskStatusEnum::ToDo->value,
            assignedUserId: $assignedUserId,
            createdAt: $now,
        ));

        return $aggregate;
    }

    /**
     * Reconstruct an existing aggregate from persistence.
     */
    public static function reconstitute(
        int $id,
        string $name,
        ?string $description,
        TaskStatusEnum $status,
        int $assignedUserId,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            id: $id,
            name: $name,
            description: $description,
            status: $status,
            assignedUserId: $assignedUserId,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );
    }

    public function changeStatus(
        TaskStatusEnum $newStatus,
        StatusTransitionResolverInterface $resolver,
    ): void {
        if ($this->status === $newStatus) {
            return;
        }

        $strategy = $resolver->resolve($this->status);

        if (!$strategy->canTransitionTo($newStatus)) {
            throw new InvalidStatusTransitionException($this->status, $newStatus);
        }

        $oldStatus = $this->status->value;
        $this->status = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new TaskStatusChangedEvent(
            oldStatus: $oldStatus,
            newStatus: $newStatus->value,
            task: $this->toArray(),
        ));
    }

    public function update(UpdateTaskDto $dto): void
    {
        $before = $this->toArray();

        $this->name = $dto->name;
        $this->description = $dto->description;
        if (null !== $dto->assignedUserId) {
            $this->assignedUserId = $dto->assignedUserId;
        }
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new TaskUpdatedEvent(
            before: $before,
            after: $this->toArray(),
        ));
    }

    /**
     * @return TaskDomainEventInterface[]
     */
    public function pullRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }

    private function recordEvent(TaskDomainEventInterface $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
            'assignedUserId' => $this->assignedUserId,
            'createdAt' => $this->createdAt->format('c'),
            'updatedAt' => $this->updatedAt->format('c'),
        ];
    }

    // ------------------------------------------------------------------
    // Getters
    // ------------------------------------------------------------------

    public function getId(): int
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

    public function getAssignedUserId(): int
    {
        return $this->assignedUserId;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
