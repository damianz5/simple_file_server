<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\FileCollectionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome!'
        ]);
    }

    #[Route('/api/list/{name}', name: 'list_action', requirements: ["name" => "^[a-f0-9]{32}$"], methods: 'GET')]
    public function listAction(string $name): JsonResponse
    {
        try {
            return $this->listFiles($name);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function listFiles(string $name): JsonResponse
    {
        $this->checkCredentials();
        $files = $this->get(FileCollectionManager::class)->listFiles($name);

        return new JsonResponse([
            'status' => 'ok',
            'collection_name' => $name,
            'files' => $files,
        ]);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            FileCollectionManager::class,
        ]);
    }
}
