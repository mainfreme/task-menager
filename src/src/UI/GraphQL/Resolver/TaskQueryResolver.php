<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Application\Message\TaskEventRecordedMessage;
use App\Domain\Model\Task\Event\TaskEventType;
use App\Domain\Model\Task\TaskAggregate;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;

final class TaskQueryResolver
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $task = $this->taskRepository->findById($id);

        if (null === $task) {
            return null;
        }

        if (!$this->isAdmin() && $task->getAssignedUserId() !== $this->getCurrentUserId()) {
            return null;
        }

        $result = $this->toGraphQL($task);

        $this->messageBus->dispatch(new TaskEventRecordedMessage(
            taskId: $task->getId(),
            eventType: TaskEventType::Viewed,
            payload: ['task' => $result],
            userId: $this->getCurrentUserId(),
        ));

        return $result;
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findAll(): array
    {
        $tasks = $this->isAdmin()
            ? $this->taskRepository->findAll()
            : $this->taskRepository->findByUserId($this->getCurrentUserId());

        return array_map(
            fn (TaskAggregate $task): array => $this->toGraphQL($task),
            $tasks,
        );
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findByUserId(int $userId): array
    {
        if (!$this->isAdmin() && $userId !== $this->getCurrentUserId()) {
            return [];
        }

        return array_map(
            fn (TaskAggregate $task): array => $this->toGraphQL($task),
            $this->taskRepository->findByUserId($userId),
        );
    }

    private function isAdmin(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function getCurrentUserId(): int
    {
        $user = $this->security->getUser();

        if (!$user instanceof UserEntity || null === $user->getId()) {
            throw new \LogicException('Authenticated user must have an ID.');
        }

        return $user->getId();
    }

    /**
     * @return array<string, mixed>
     */
    private function toGraphQL(TaskAggregate $task): array
    {
        return [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus()->value,
            'assignedUserId' => $task->getAssignedUserId(),
            'createdAt' => $task->getCreatedAt()->format('c'),
            'updatedAt' => $task->getUpdatedAt()->format('c'),
        ];
    }
}
