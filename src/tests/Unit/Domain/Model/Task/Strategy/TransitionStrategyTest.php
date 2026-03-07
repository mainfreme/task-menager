<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Model\Task\Strategy;

use App\Domain\Model\Enum\TaskStatusEnum;
use App\Domain\Model\Task\Strategy\DoneTransitionStrategy;
use App\Domain\Model\Task\Strategy\InProgressTransitionStrategy;
use App\Domain\Model\Task\Strategy\StatusTransitionStrategyInterface;
use App\Domain\Model\Task\Strategy\ToDoTransitionStrategy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransitionStrategyTest extends TestCase
{
    // ------------------------------------------------------------------
    // getStatus() — each strategy reports its own status correctly
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('strategyStatusProvider')]
    public function reportsOwnStatus(
        StatusTransitionStrategyInterface $strategy,
        TaskStatusEnum $expectedStatus,
    ): void {
        $this->assertSame($expectedStatus, $strategy->getStatus());
    }

    /** @return iterable<string, array{StatusTransitionStrategyInterface, TaskStatusEnum}> */
    public static function strategyStatusProvider(): iterable
    {
        yield 'ToDo strategy' => [new ToDoTransitionStrategy(), TaskStatusEnum::ToDo];
        yield 'InProgress strategy' => [new InProgressTransitionStrategy(), TaskStatusEnum::InProgress];
        yield 'Done strategy' => [new DoneTransitionStrategy(), TaskStatusEnum::Done];
    }

    // ------------------------------------------------------------------
    // Full transition matrix — every (from, to) pair is covered
    // ------------------------------------------------------------------

    #[Test]
    #[DataProvider('transitionMatrixProvider')]
    public function evaluatesTransitionCorrectly(
        StatusTransitionStrategyInterface $strategy,
        TaskStatusEnum $target,
        bool $expected,
    ): void {
        $this->assertSame($expected, $strategy->canTransitionTo($target));
    }

    /**
     * Exhaustive matrix of all 9 (from × to) combinations.
     *
     * Allowed transitions:
     *   ToDo       -> InProgress
     *   InProgress -> ToDo, Done
     *   Done       -> ToDo
     *
     * @return iterable<string, array{StatusTransitionStrategyInterface, TaskStatusEnum, bool}>
     */
    public static function transitionMatrixProvider(): iterable
    {
        $toDo = new ToDoTransitionStrategy();
        $inProgress = new InProgressTransitionStrategy();
        $done = new DoneTransitionStrategy();

        // --- ToDo ---
        yield 'ToDo -> ToDo'       => [$toDo, TaskStatusEnum::ToDo, false];
        yield 'ToDo -> InProgress'  => [$toDo, TaskStatusEnum::InProgress, true];
        yield 'ToDo -> Done'        => [$toDo, TaskStatusEnum::Done, false];

        // --- InProgress ---
        yield 'InProgress -> ToDo'       => [$inProgress, TaskStatusEnum::ToDo, true];
        yield 'InProgress -> InProgress'  => [$inProgress, TaskStatusEnum::InProgress, false];
        yield 'InProgress -> Done'        => [$inProgress, TaskStatusEnum::Done, true];

        // --- Done ---
        yield 'Done -> ToDo'       => [$done, TaskStatusEnum::ToDo, true];
        yield 'Done -> InProgress'  => [$done, TaskStatusEnum::InProgress, false];
        yield 'Done -> Done'        => [$done, TaskStatusEnum::Done, false];
    }

    // ------------------------------------------------------------------
    // Guard: every enum case is represented in the matrix
    // ------------------------------------------------------------------

    #[Test]
    public function transitionMatrixCoversAllEnumCases(): void
    {
        $allCases = TaskStatusEnum::cases();
        $strategies = [
            new ToDoTransitionStrategy(),
            new InProgressTransitionStrategy(),
            new DoneTransitionStrategy(),
        ];

        $this->assertCount(
            count($allCases),
            $strategies,
            'Number of strategies should match number of TaskStatusEnum cases — did you add a new status?',
        );

        foreach ($strategies as $strategy) {
            $this->assertContains(
                $strategy->getStatus(),
                $allCases,
                sprintf('Strategy reports unknown status "%s".', $strategy->getStatus()->value),
            );
        }
    }
}
