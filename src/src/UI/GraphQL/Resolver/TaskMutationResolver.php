<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Application\DTO\UpdateTaskDto;
use App\Application\Message\TaskEventRecordedMessage;
use App\Application\Validator\TaskInputValidator;
use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\Event\TaskDomainEventInterface;
use App\Domain\Model\Task\Strategy\StatusTransitionResolverInterface;
use App\Domain\Model\Task\TaskAggregate;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;

final class TaskMutationResolver
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly MessageBusInterface $messageBus,
        private readonly StatusTransitionResolverInterface $statusTransitionResolver,
        private readonly TaskInputValidator $validator,
        private readonly Security $security,
    ) {
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function createTask(array $input): array
    {
        $this->validator->validateCreate($input);

        $task = TaskAggregate::create(
            name: $input['name'],
            description: $input['description'] ?? null,
            assignedUserId: $input['assignedUserId'],
        );

        $recordedEvents = $task->pullRecordedEvents();
        $task = $this->taskRepository->saveAndReturn($task);
        $this->persistRecordedEventsWithTaskId($task->getId(), $recordedEvents);

        return $this->toGraphQL($task);
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    public function updateTask(int $id, array $input): array
    {
        $this->validator->validateUpdate($input);

        $task = $this->taskRepository->get($id);

        $dto = new UpdateTaskDto(
            name: $input['name'],
            description: $input['description'] ?? null,
            assignedUserId: $input['assignedUserId'] ?? null,
        );

        $task->update($dto);

        $this->taskRepository->update($task);
        $this->taskRepository->flush();

        $this->persistRecordedEvents($task);

        return $this->toGraphQL($task);
    }

    /**
     * @return array<string, mixed>
     */
    public function changeTaskStatus(int $id, string $status): array
    {
        $task = $this->taskRepository->get($id);

        $newStatus = TaskStatusEnum::from($status);
        $task->changeStatus($newStatus, $this->statusTransitionResolver);

        $this->taskRepository->update($task);
        $this->taskRepository->flush();

        $this->persistRecordedEvents($task);

        return $this->toGraphQL($task);
    }

    public function deleteTask(int $id): bool
    {
        $this->taskRepository->delete($id);
        $this->taskRepository->flush();

        return true;
    }

    private function persistRecordedEvents(TaskAggregate $task): void
    {
        $this->persistRecordedEventsWithTaskId($task->getId(), $task->pullRecordedEvents());
    }

    /**
     * @param TaskDomainEventInterface[] $events
     */
    /**
     * @param TaskDomainEventInterface[] $events
     */
    private function persistRecordedEventsWithTaskId(int $taskId, array $events): void
    {
        $userId = $this->getCurrentUserId();

        foreach ($events as $event) {
            $this->messageBus->dispatch(new TaskEventRecordedMessage(
                taskId: $taskId,
                eventType: $event->eventType(),
                payload: $event->toPayload(),
                userId: $userId,
            ));
        }
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
