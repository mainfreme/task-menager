<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Model\Task\Event\TaskEventType;
use App\Domain\Repository\TaskEventRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\TaskEntity;
use App\Infrastructure\Persistence\Doctrine\Entity\TaskEventEntity;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineTaskEventRepository implements TaskEventRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return array<array<string, mixed>>
     */
    public function findByTaskId(int $taskId): array
    {
        $entities = $this->entityManager->createQueryBuilder()
            ->select('e')
            ->from(TaskEventEntity::class, 'e')
            ->join('e.task', 't')
            ->where('t.id = :taskId')
            ->setParameter('taskId', $taskId)
            ->orderBy('e.occurredAt', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (TaskEventEntity $entity): array => [
                'id' => $entity->getId(),
                'taskId' => $entity->getTaskId(),
                'eventType' => $entity->getEventType(),
                'payload' => json_encode($entity->getPayload(), \JSON_THROW_ON_ERROR),
                'userId' => $entity->getUserId(),
                'occurredAt' => $entity->getOccurredAt()->format('c'),
            ],
            $entities,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function record(int $taskId, TaskEventType $type, array $payload, int $userId): void
    {
        $taskEntity = $this->entityManager->getRepository(TaskEntity::class)->find($taskId);
        $userEntity = $this->entityManager->getRepository(UserEntity::class)->find($userId);

        if (null === $taskEntity || null === $userEntity) {
            return;
        }

        $event = new TaskEventEntity(
            task: $taskEntity,
            eventType: $type,
            payload: $payload,
            user: $userEntity,
            occurredAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($event);
        $this->entityManager->flush();
    }
}
