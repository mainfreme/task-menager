<?php

declare(strict_types=1);

namespace App\Tests\Unit\UI\GraphQL\Resolver;

use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\TaskAggregate;
use App\Domain\Model\User\UserAggregate;
use App\Domain\Repository\TaskRepositoryInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;
use App\Infrastructure\Persistence\Doctrine\Entity\UserEntity;
use App\UI\GraphQL\Resolver\TaskQueryResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class TaskQueryResolverTest extends TestCase
{
    private MockObject&TaskRepositoryInterface $taskRepository;
    private MockObject&MessageBusInterface $messageBus;
    private MockObject&Security $security;
    private TaskQueryResolver $resolver;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->messageBus->method('dispatch')->willReturn(new Envelope(new \stdClass()));
        $this->security = $this->createMock(Security::class);

        $this->resolver = new TaskQueryResolver(
            $this->taskRepository,
            $this->messageBus,
            $this->security,
        );
    }

    // --- findAll ---

    #[Test]
    public function findAllAsAdminReturnsAllTasks(): void
    {
        $adminUser = $this->createUserEntity(1, 'admin');
        $task1 = $this->createTask(1, 10);
        $task2 = $this->createTask(2, 20);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->security->method('getUser')->willReturn($adminUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([$task1, $task2]);

        $this->taskRepository
            ->expects($this->never())
            ->method('findByUserId');

        $result = $this->resolver->findAll();

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(2, $result[1]['id']);
    }

    #[Test]
    public function findAllAsRegularUserReturnsOnlyOwnTasks(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');
        $ownTask = $this->createTask(1, 5);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->never())
            ->method('findAll');

        $this->taskRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with(5)
            ->willReturn([$ownTask]);

        $result = $this->resolver->findAll();

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(5, $result[0]['assignedUserId']);
    }

    #[Test]
    public function findAllAsRegularUserWithNoTasksReturnsEmptyArray(): void
    {
        $currentUser = $this->createUserEntity(99, 'newuser');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with(99)
            ->willReturn([]);

        $result = $this->resolver->findAll();

        $this->assertSame([], $result);
    }

    // --- findById ---

    #[Test]
    public function findByIdAsAdminReturnsAnyTask(): void
    {
        $adminUser = $this->createUserEntity(1, 'admin');
        $task = $this->createTask(42, 999);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->security->method('getUser')->willReturn($adminUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findById')
            ->with(42)
            ->willReturn($task);

        $result = $this->resolver->findById(42);

        $this->assertNotNull($result);
        $this->assertSame(42, $result['id']);
        $this->assertSame(999, $result['assignedUserId']);
    }

    #[Test]
    public function findByIdAsRegularUserReturnsTaskWhenAssignedToHim(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');
        $ownTask = $this->createTask(10, 5);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($ownTask);

        $result = $this->resolver->findById(10);

        $this->assertNotNull($result);
        $this->assertSame(10, $result['id']);
        $this->assertSame(5, $result['assignedUserId']);
    }

    #[Test]
    public function findByIdAsRegularUserReturnsNullWhenTaskAssignedToOtherUser(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');
        $otherUserTask = $this->createTask(10, 99);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findById')
            ->with(10)
            ->willReturn($otherUserTask);

        $result = $this->resolver->findById(10);

        $this->assertNull($result);
    }

    #[Test]
    public function findByIdReturnsNullWhenTaskDoesNotExist(): void
    {
        $adminUser = $this->createUserEntity(1, 'admin');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->security->method('getUser')->willReturn($adminUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->resolver->findById(999);

        $this->assertNull($result);
    }

    #[Test]
    public function findByIdAsRegularUserReturnsNullForNonExistentTask(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);

        $result = $this->resolver->findById(999);

        $this->assertNull($result);
    }

    // --- findByUserId ---

    #[Test]
    public function findByUserIdAsAdminReturnsTasksForAnyUser(): void
    {
        $adminUser = $this->createUserEntity(1, 'admin');
        $tasks = [
            $this->createTask(1, 50),
            $this->createTask(2, 50),
        ];

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->security->method('getUser')->willReturn($adminUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with(50)
            ->willReturn($tasks);

        $result = $this->resolver->findByUserId(50);

        $this->assertCount(2, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame(2, $result[1]['id']);
    }

    #[Test]
    public function findByUserIdAsRegularUserReturnsOwnTasks(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');
        $ownTasks = [$this->createTask(1, 5)];

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with(5)
            ->willReturn($ownTasks);

        $result = $this->resolver->findByUserId(5);

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
    }

    #[Test]
    public function findByUserIdAsRegularUserReturnsEmptyWhenRequestingOtherUserTasks(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->never())
            ->method('findByUserId');

        $result = $this->resolver->findByUserId(99);

        $this->assertSame([], $result);
    }

    #[Test]
    public function findByUserIdAsRegularUserReturnsEmptyWhenRequestingOwnUserIdWithNoTasks(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with(5)
            ->willReturn([]);

        $result = $this->resolver->findByUserId(5);

        $this->assertSame([], $result);
    }

    // --- Edge cases ---

    #[Test]
    public function findByIdAsRegularUserWithSameUserIdAsAssignedReturnsTask(): void
    {
        $currentUser = $this->createUserEntity(1, 'user1');
        $task = $this->createTask(1, 1);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($task);

        $result = $this->resolver->findById(1);

        $this->assertNotNull($result);
        $this->assertSame(1, $result['id']);
    }

    #[Test]
    public function findByUserIdWithZeroUserIdAsRegularUserReturnsEmptyWhenCurrentUserIsNotZero(): void
    {
        $currentUser = $this->createUserEntity(5, 'johndoe');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($currentUser);

        $this->taskRepository
            ->expects($this->never())
            ->method('findByUserId');

        $result = $this->resolver->findByUserId(0);

        $this->assertSame([], $result);
    }

    #[Test]
    public function findByUserIdAsAdminWithZeroUserIdCallsRepository(): void
    {
        $adminUser = $this->createUserEntity(1, 'admin');

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->security->method('getUser')->willReturn($adminUser);

        $this->taskRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with(0)
            ->willReturn([]);

        $result = $this->resolver->findByUserId(0);

        $this->assertSame([], $result);
    }

    #[Test]
    public function findAllThrowsWhenUserIsNull(): void
    {
        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn(null);

        $this->taskRepository->expects($this->never())->method('findAll');
        $this->taskRepository->expects($this->never())->method('findByUserId');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Authenticated user must have an ID.');

        $this->resolver->findAll();
    }

    #[Test]
    public function findAllThrowsWhenUserHasNullId(): void
    {
        $userWithoutId = $this->createUserEntity(1, 'user');
        $reflection = new \ReflectionClass($userWithoutId);
        $property = $reflection->getProperty('id');
        $property->setValue($userWithoutId, null);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(false);
        $this->security->method('getUser')->willReturn($userWithoutId);

        $this->taskRepository->expects($this->never())->method('findAll');
        $this->taskRepository->expects($this->never())->method('findByUserId');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Authenticated user must have an ID.');

        $this->resolver->findAll();
    }

    #[Test]
    public function findByIdReturnsCorrectGraphQLStructure(): void
    {
        $adminUser = $this->createUserEntity(1, 'admin');
        $task = $this->createTask(42, 10);

        $this->security->method('isGranted')->with('ROLE_ADMIN')->willReturn(true);
        $this->security->method('getUser')->willReturn($adminUser);

        $this->taskRepository->method('findById')->with(42)->willReturn($task);

        $result = $this->resolver->findById(42);

        $this->assertNotNull($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('description', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertArrayHasKey('assignedUserId', $result);
        $this->assertArrayHasKey('createdAt', $result);
        $this->assertArrayHasKey('updatedAt', $result);
        $this->assertSame('Task 42', $result['name']);
        $this->assertSame('To Do', $result['status']);
    }

    private function createTask(int $id, int $assignedUserId): TaskAggregate
    {
        $now = new \DateTimeImmutable();

        return TaskAggregate::reconstitute(
            id: $id,
            name: "Task {$id}",
            description: "Description {$id}",
            status: TaskStatusEnum::ToDo,
            assignedUserId: $assignedUserId,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    private function createUserEntity(int $id, string $username): UserEntity
    {
        $userAggregate = UserAggregate::create(
            name: 'Test User',
            username: Username::fromString($username),
            email: Email::fromString("{$username}@test.pl"),
            address: null,
            phone: null,
            website: null,
            company: null,
            passwordHash: 'hashed',
        );

        $entity = UserEntity::fromDomain($userAggregate);

        $reflection = new \ReflectionClass($entity);
        $property = $reflection->getProperty('id');
        $property->setValue($entity, $id);

        return $entity;
    }
}
