<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Task;

use App\Domain\Exception\InvalidStatusTransitionException;
use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\Strategy\DoneTransitionStrategy;
use App\Domain\Model\Task\Strategy\InProgressTransitionStrategy;
use App\Domain\Model\Task\Strategy\StatusTransitionResolver;
use App\Domain\Model\Task\Strategy\StatusTransitionResolverInterface;
use App\Domain\Model\Task\Strategy\ToDoTransitionStrategy;
use App\Domain\Model\Task\TaskAggregate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TaskChangeStatusTest extends TestCase
{
    private StatusTransitionResolverInterface $resolver;

    protected function setUp(): void
    {
        $this->resolver = new StatusTransitionResolver([
            new ToDoTransitionStrategy(),
            new InProgressTransitionStrategy(),
            new DoneTransitionStrategy(),
        ]);
    }

    // ------------------------------------------------------------------
    // Happy-path transitions
    // ------------------------------------------------------------------

    #[Test]
    public function transitionsFromToDoToInProgress(): void
    {
        $task = $this->createTaskWithStatus(TaskStatusEnum::ToDo);

        $task->changeStatus(TaskStatusEnum::InProgress, $this->resolver);

        $this->assertSame(TaskStatusEnum::InProgress, $task->getStatus());
    }

    #[Test]
    public function transitionsFromInProgressToToDo(): void
    {
        $task = $this->createTaskWithStatus(TaskStatusEnum::InProgress);

        $task->changeStatus(TaskStatusEnum::ToDo, $this->resolver);

        $this->assertSame(TaskStatusEnum::ToDo, $task->getStatus());
    }

    #[Test]
    public function transitionsFromInProgressToDone(): void
    {
        $task = $this->createTaskWithStatus(TaskStatusEnum::InProgress);

        $task->changeStatus(TaskStatusEnum::Done, $this->resolver);

        $this->assertSame(TaskStatusEnum::Done, $task->getStatus());
    }

    #[Test]
    public function transitionsFromDoneToToDo(): void
    {
        $task = $this->createTaskWithStatus(TaskStatusEnum::Done);

        $task->changeStatus(TaskStatusEnum::ToDo, $this->resolver);

        $this->assertSame(TaskStatusEnum::ToDo, $task->getStatus());
    }

    // ------------------------------------------------------------------
    // Full workflow: ToDo -> InProgress -> Done -> ToDo
    // ------------------------------------------------------------------

    #[Test]
    public function supportsFullLifecycleTransition(): void
    {
        $task = TaskAggregate::create(1, 'Lifecycle task', null, 10);
        $this->assertSame(TaskStatusEnum::ToDo, $task->getStatus());

        $task->changeStatus(TaskStatusEnum::InProgress, $this->resolver);
        $this->assertSame(TaskStatusEnum::InProgress, $task->getStatus());

        $task->changeStatus(TaskStatusEnum::Done, $this->resolver);
        $this->assertSame(TaskStatusEnum::Done, $task->getStatus());

        $task->changeStatus(TaskStatusEnum::ToDo, $this->resolver);
        $this->assertSame(TaskStatusEnum::ToDo, $task->getStatus());
    }

    // ------------------------------------------------------------------
    // Edge case: same status (early return, no exception)
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('sameStatusProvider')]
    public function changingToSameStatusIsNoOp(TaskStatusEnum $status): void
    {
        $task = $this->createTaskWithStatus($status);
        $updatedAtBefore = $task->getUpdatedAt();

        $task->changeStatus($status, $this->resolver);

        $this->assertSame($status, $task->getStatus());
        $this->assertSame($updatedAtBefore, $task->getUpdatedAt());
    }

    /** @return array<string, array{TaskStatusEnum}> */
    public static function sameStatusProvider(): array
    {
        return [
            'ToDo -> ToDo' => [TaskStatusEnum::ToDo],
            'InProgress -> InProgress' => [TaskStatusEnum::InProgress],
            'Done -> Done' => [TaskStatusEnum::Done],
        ];
    }

    // ------------------------------------------------------------------
    // Forbidden transitions
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('forbiddenTransitionsProvider')]
    public function throwsOnForbiddenTransition(TaskStatusEnum $from, TaskStatusEnum $to): void
    {
        $task = $this->createTaskWithStatus($from);

        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage(
            sprintf('Cannot transition task status from "%s" to "%s".', $from->value, $to->value)
        );

        $task->changeStatus($to, $this->resolver);
    }

    /** @return array<string, array{TaskStatusEnum, TaskStatusEnum}> */
    public static function forbiddenTransitionsProvider(): array
    {
        return [
            'ToDo -> Done' => [TaskStatusEnum::ToDo, TaskStatusEnum::Done],
            'Done -> InProgress' => [TaskStatusEnum::Done, TaskStatusEnum::InProgress],
        ];
    }

    // ------------------------------------------------------------------
    // updatedAt is refreshed on valid transition
    // ------------------------------------------------------------------

    #[Test]
    public function updatesTimestampOnSuccessfulTransition(): void
    {
        $past = new \DateTimeImmutable('2020-01-01 00:00:00');
        $task = TaskAggregate::reconstitute(
            1,
            'Test',
            null,
            TaskStatusEnum::ToDo,
            10,
            $past,
            $past,
        );

        $task->changeStatus(TaskStatusEnum::InProgress, $this->resolver);

        $this->assertGreaterThan($past, $task->getUpdatedAt());
    }

    #[Test]
    public function doesNotUpdateTimestampOnSameStatus(): void
    {
        $past = new \DateTimeImmutable('2020-01-01 00:00:00');
        $task = TaskAggregate::reconstitute(
            1,
            'Test',
            null,
            TaskStatusEnum::ToDo,
            10,
            $past,
            $past,
        );

        $task->changeStatus(TaskStatusEnum::ToDo, $this->resolver);

        $this->assertSame($past, $task->getUpdatedAt());
    }

    // ------------------------------------------------------------------
    // Status does not change when exception is thrown
    // ------------------------------------------------------------------

    #[Test]
    public function statusRemainsUnchangedAfterForbiddenTransition(): void
    {
        $task = $this->createTaskWithStatus(TaskStatusEnum::ToDo);

        try {
            $task->changeStatus(TaskStatusEnum::Done, $this->resolver);
        } catch (InvalidStatusTransitionException) {
        }

        $this->assertSame(TaskStatusEnum::ToDo, $task->getStatus());
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function createTaskWithStatus(TaskStatusEnum $status): TaskAggregate
    {
        $now = new \DateTimeImmutable();

        return TaskAggregate::reconstitute(
            1,
            'Test task',
            'Description',
            $status,
            10,
            $now,
            $now,
        );
    }
}
