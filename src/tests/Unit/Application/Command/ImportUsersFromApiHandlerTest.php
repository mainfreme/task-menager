<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Command;

use App\Application\Command\ImportUsersFromApiCommand;
use App\Application\Command\ImportUsersFromApiHandler;
use App\Application\DTO\ImportUsersResult;
use App\Domain\Exception\UserCreationException;
use App\Domain\Factory\UserFactoryInterface;
use App\Domain\Model\User\User;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\UserApiAdapterInterface;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\Username;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ImportUsersFromApiHandlerTest extends TestCase
{
    private MockObject&UserApiAdapterInterface $adapter;
    private MockObject&UserFactoryInterface $factory;
    private MockObject&UserRepositoryInterface $repository;
    private LoggerInterface $logger;
    private ImportUsersFromApiHandler $handler;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(UserApiAdapterInterface::class);
        $this->factory = $this->createMock(UserFactoryInterface::class);
        $this->repository = $this->createMock(UserRepositoryInterface::class);
        $this->logger = new NullLogger();

        $this->handler = new ImportUsersFromApiHandler(
            $this->adapter,
            $this->factory,
            $this->repository,
            $this->logger
        );
    }

    #[Test]
    public function importsNewUsersSuccessfully(): void
    {
        $apiData = [
            [
                'id' => 1,
                'name' => 'User One',
                'email' => 'user1@example.com',
            ],
            [
                'id' => 2,
                'name' => 'User Two',
                'email' => 'user2@example.com',
            ],
        ];

        $this->adapter
            ->expects($this->once())
            ->method('fetchUsers')
            ->willReturn($apiData);

        $this->repository
            ->expects($this->exactly(2))
            ->method('existUserByEmail')
            ->willReturn(false);

        $user1 = $this->createTestUser('userone', 'user1@example.com');
        $user2 = $this->createTestUser('usertwo', 'user2@example.com');

        $this->factory
            ->expects($this->exactly(2))
            ->method('createFromApiData')
            ->willReturnOnConsecutiveCalls($user1, $user2);

        $this->repository
            ->expects($this->exactly(2))
            ->method('save');

        $this->repository
            ->expects($this->once())
            ->method('flush');

        $command = new ImportUsersFromApiCommand();
        $result = $this->handler->handle($command);

        $this->assertInstanceOf(ImportUsersResult::class, $result);
        $this->assertSame(2, $result->imported);
        $this->assertSame(0, $result->skipped);
        $this->assertSame(0, $result->failed);
        $this->assertTrue($result->isSuccess());
    }

    #[Test]
    public function skipsExistingUsers(): void
    {
        $apiData = [
            [
                'id' => 1,
                'name' => 'Existing User',
                'email' => 'existing@example.com',
            ],
        ];

        $this->adapter
            ->expects($this->once())
            ->method('fetchUsers')
            ->willReturn($apiData);

        $this->repository
            ->expects($this->once())
            ->method('existUserByEmail')
            ->willReturn(true);

        $this->factory
            ->expects($this->never())
            ->method('createFromApiData');

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->repository
            ->expects($this->once())
            ->method('flush');

        $command = new ImportUsersFromApiCommand();
        $result = $this->handler->handle($command);

        $this->assertSame(0, $result->imported);
        $this->assertSame(1, $result->skipped);
        $this->assertSame(0, $result->failed);
    }

    #[Test]
    public function handlesUserCreationErrorsGracefully(): void
    {
        $apiData = [
            [
                'id' => 1,
                'name' => 'Invalid User',
                'email' => 'valid@example.com',
            ],
        ];

        $this->adapter
            ->expects($this->once())
            ->method('fetchUsers')
            ->willReturn($apiData);

        $this->repository
            ->expects($this->once())
            ->method('existUserByEmail')
            ->willReturn(false);

        $this->factory
            ->expects($this->once())
            ->method('createFromApiData')
            ->willThrowException(new UserCreationException('Missing required field'));

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->repository
            ->expects($this->once())
            ->method('flush');

        $command = new ImportUsersFromApiCommand();
        $result = $this->handler->handle($command);

        $this->assertSame(0, $result->imported);
        $this->assertSame(0, $result->skipped);
        $this->assertSame(1, $result->failed);
        $this->assertFalse($result->isSuccess());
        $this->assertCount(1, $result->errors);
    }

    #[Test]
    public function throwsExceptionWhenApiFetchFails(): void
    {
        $this->adapter
            ->expects($this->once())
            ->method('fetchUsers')
            ->willThrowException(new \RuntimeException('API connection failed'));

        $this->factory
            ->expects($this->never())
            ->method('createFromApiData');

        $this->repository
            ->expects($this->never())
            ->method('save');

        $this->repository
            ->expects($this->never())
            ->method('flush');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('API connection failed');

        $command = new ImportUsersFromApiCommand();
        $this->handler->handle($command);
    }

    #[Test]
    public function handlesMixedResults(): void
    {
        $apiData = [
            ['id' => 1, 'name' => 'User One', 'email' => 'user1@example.com'],
            ['id' => 2, 'name' => 'User Two', 'email' => 'existing@example.com'],
            ['id' => 3, 'name' => 'User Three', 'email' => 'invalid@example.com'],
        ];

        $this->adapter
            ->expects($this->once())
            ->method('fetchUsers')
            ->willReturn($apiData);

        $this->repository
            ->expects($this->exactly(3))
            ->method('existUserByEmail')
            ->willReturnOnConsecutiveCalls(false, true, false);

        $user1 = $this->createTestUser('userone', 'user1@example.com');

        $this->factory
            ->expects($this->exactly(2))
            ->method('createFromApiData')
            ->willReturnCallback(function ($data) use ($user1) {
                if ('invalid@example.com' === $data['email']) {
                    throw new UserCreationException('Invalid user data');
                }

                return $user1;
            });

        $this->repository
            ->expects($this->once())
            ->method('save');

        $this->repository
            ->expects($this->once())
            ->method('flush');

        $command = new ImportUsersFromApiCommand();
        $result = $this->handler->handle($command);

        $this->assertSame(1, $result->imported);
        $this->assertSame(1, $result->skipped);
        $this->assertSame(1, $result->failed);
    }

    private function createTestUser(string $username, string $email): User
    {
        return User::create(
            name: 'Test User',
            username: Username::fromString($username),
            email: Email::fromString($email),
            address: null,
            phone: null,
            website: null,
            company: null,
            passwordHash: 'hashed_password',
        );
    }
}
