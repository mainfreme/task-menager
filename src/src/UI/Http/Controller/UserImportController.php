<?php

declare(strict_types=1);

namespace App\UI\Http\Controller;

use App\Application\Command\ImportUsersFromApiCommand;
use App\Application\Command\ImportUsersFromApiHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserImportController extends AbstractController
{
    public function __construct(
        private readonly ImportUsersFromApiHandler $importHandler,
    ) {
    }

    #[Route('/api/import-users', name: 'api_import_users', methods: ['POST'])]
    public function importUsers(): JsonResponse
    {
        try {
            $command = new ImportUsersFromApiCommand();
            $result = $this->importHandler->handle($command);

            $status = $result->isSuccess() ? 'success' : 'partial_success';
            $httpStatus = $result->isSuccess() ? Response::HTTP_OK : Response::HTTP_MULTI_STATUS;

            return new JsonResponse([
                'status' => $status,
                'imported' => $result->imported,
                'skipped' => $result->skipped,
                'failed' => $result->failed,
                'errors' => $result->errors,
            ], $httpStatus);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Failed to import users: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
