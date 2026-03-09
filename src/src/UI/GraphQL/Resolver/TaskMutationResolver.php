<?php

declare(strict_types=1);

namespace App\UI\GraphQL\Resolver;

use App\Application\DTO\UpdateTaskDto;
use App\Application\Validator\TaskInputValidator;
use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\Strategy\StatusTransitionResolverInterface;
use App\Domain\Model\Task\TaskAggregate;
use App\Domain\Repository\TaskRepositoryInterface;

final class TaskMutationResolver
{
    public function __construct(
        private readonly TaskRepositoryInterface $taskRepository,
        private readonly StatusTransitionResolverInterface $statusTransitionResolver,
        private readonly TaskInputValidator $validator,
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

        $task = $this->taskRepository->saveAndReturn($task);

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

        return $this->toGraphQL($task);
    }

    public function deleteTask(int $id): bool
    {
        $this->taskRepository->delete($id);
        $this->taskRepository->flush();

        return true;
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
