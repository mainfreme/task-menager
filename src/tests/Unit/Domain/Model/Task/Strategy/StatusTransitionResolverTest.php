<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\Strategy\DoneTransitionStrategy;
use App\Domain\Model\Task\Strategy\InProgressTransitionStrategy;
use App\Domain\Model\Task\Strategy\StatusTransitionResolver;
use App\Domain\Model\Task\Strategy\ToDoTransitionStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class StatusTransitionResolverTest extends TestCase
{
    private StatusTransitionResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new StatusTransitionResolver([
            new ToDoTransitionStrategy(),
            new InProgressTransitionStrategy(),
            new DoneTransitionStrategy(),
        ]);
    }

    #[Test]
    public function resolvesToDoStrategy(): void
    {
        $strategy = $this->resolver->resolve(TaskStatusEnum::ToDo);

        $this->assertInstanceOf(ToDoTransitionStrategy::class, $strategy);
    }

    #[Test]
    public function resolvesInProgressStrategy(): void
    {
        $strategy = $this->resolver->resolve(TaskStatusEnum::InProgress);

        $this->assertInstanceOf(InProgressTransitionStrategy::class, $strategy);
    }

    #[Test]
    public function resolvesDoneStrategy(): void
    {
        $strategy = $this->resolver->resolve(TaskStatusEnum::Done);

        $this->assertInstanceOf(DoneTransitionStrategy::class, $strategy);
    }

    #[Test]
    public function throwsWhenNoStrategyRegistered(): void
    {
        $emptyResolver = new StatusTransitionResolver([]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No transition strategy found for status "To Do".');

        $emptyResolver->resolve(TaskStatusEnum::ToDo);
    }

    #[Test]
    public function returnsFirstMatchingStrategyWhenDuplicatesExist(): void
    {
        $first = new ToDoTransitionStrategy();
        $second = new ToDoTransitionStrategy();

        $resolver = new StatusTransitionResolver([$first, $second]);
        $resolved = $resolver->resolve(TaskStatusEnum::ToDo);

        $this->assertSame($first, $resolved);
    }

    #[Test]
    #[DataProvider('partialRegistrationProvider')]
    public function throwsForUnregisteredStatusInPartialResolver(TaskStatusEnum $missing): void
    {
        $resolver = new StatusTransitionResolver([
            new ToDoTransitionStrategy(),
        ]);

        $this->expectException(\InvalidArgumentException::class);

        $resolver->resolve($missing);
    }

    /** @return array<string, array{TaskStatusEnum}> */
    public static function partialRegistrationProvider(): array
    {
        return [
            'InProgress not registered' => [TaskStatusEnum::InProgress],
            'Done not registered' => [TaskStatusEnum::Done],
        ];
    }

    // ------------------------------------------------------------------
    // Resolve + canTransitionTo — realistic usage through the resolver
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('resolveAndTransitionProvider')]
    public function resolvesThenEvaluatesTransition(
        TaskStatusEnum $current,
        TaskStatusEnum $target,
        bool $expected,
    ): void {
        $strategy = $this->resolver->resolve($current);

        $this->assertSame($expected, $strategy->canTransitionTo($target));
    }

    /**
     * Tests the resolver in its intended usage pattern: resolve a strategy
     * for the current status, then ask it whether a transition is allowed.
     *
     * @return iterable<string, array{TaskStatusEnum, TaskStatusEnum, bool}>
     */
    public static function resolveAndTransitionProvider(): iterable
    {
        // allowed
        yield 'ToDo -> InProgress (allowed)'       => [TaskStatusEnum::ToDo, TaskStatusEnum::InProgress, true];
        yield 'InProgress -> ToDo (allowed)'        => [TaskStatusEnum::InProgress, TaskStatusEnum::ToDo, true];
        yield 'InProgress -> Done (allowed)'        => [TaskStatusEnum::InProgress, TaskStatusEnum::Done, true];
        yield 'Done -> ToDo (allowed)'              => [TaskStatusEnum::Done, TaskStatusEnum::ToDo, true];

        // forbidden
        yield 'ToDo -> Done (forbidden)'            => [TaskStatusEnum::ToDo, TaskStatusEnum::Done, false];
        yield 'Done -> InProgress (forbidden)'      => [TaskStatusEnum::Done, TaskStatusEnum::InProgress, false];
    }

    #[Test]
    public function resolvesStrategyForEveryEnumCase(): void
    {
        foreach (TaskStatusEnum::cases() as $status) {
            $strategy = $this->resolver->resolve($status);

            $this->assertSame(
                $status,
                $strategy->getStatus(),
                sprintf('Resolver returned wrong strategy for status "%s".', $status->value),
            );
        }
    }
}
