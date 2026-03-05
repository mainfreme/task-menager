<?php

declare(strict_types=1);

namespace App\Application\Command;

use App\Domain\Exception\UserCreationException;
use App\Domain\Factory\UserFactoryInterface;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Service\UserApiAdapterInterface;
use App\Domain\ValueObject\Email;
use Psr\Log\LoggerInterface;
use App\Application\DTO\ImportUsersResult;

final class ImportUsersFromApiHandler
{
    public function __construct(
        private readonly UserApiAdapterInterface $userApiAdapter,
        private readonly UserFactoryInterface $userFactory,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(ImportUsersFromApiCommand $command): ImportUsersResult
    {
        $imported = 0;
        $skipped = 0;
        $failed = 0;
        $errors = [];

        try {
            $usersData = $this->userApiAdapter->fetchUsers();

            foreach ($usersData as $userData) {
                try {
                    $email = Email::fromString($userData['email']);

                    if ($this->userRepository->existUserByEmail($email)) {
                        ++$skipped;
                        $this->logger->info(
                            'User with email {email} already exists, skipping',
                            ['email' => $email->getValue()]
                        );
                        continue;
                    }

                    $user = $this->userFactory->createFromApiData($userData);
                    $this->userRepository->save($user);
                    ++$imported;

                    $this->logger->info(
                        'Successfully imported user {username}',
                        ['username' => $user->getUsername()->getValue()]
                    );
                } catch (UserCreationException $e) {
                    ++$failed;
                    $errorMessage = sprintf(
                        'Failed to create user from data: %s',
                        $e->getMessage()
                    );
                    $errors[] = $errorMessage;
                    $this->logger->error($errorMessage, [
                        'exception' => $e,
                        'userData' => $userData,
                    ]);
                } catch (\Throwable $e) {
                    ++$failed;
                    $errorMessage = sprintf(
                        'Unexpected error while importing user: %s',
                        $e->getMessage()
                    );
                    $errors[] = $errorMessage;
                    $this->logger->error($errorMessage, [
                        'exception' => $e,
                        'userData' => $userData,
                    ]);
                }
            }

            $this->userRepository->flush();

            $this->logger->info('Import completed', [
                'imported' => $imported,
                'skipped' => $skipped,
                'failed' => $failed,
            ]);
        } catch (\RuntimeException $e) {
            $this->logger->error('Failed to fetch users from API', ['exception' => $e]);
            throw $e;
        }

        return new ImportUsersResult(
            imported: $imported,
            skipped: $skipped,
            failed: $failed,
            errors: $errors
        );
    }
}
