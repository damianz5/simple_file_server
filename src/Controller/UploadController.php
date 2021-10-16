<?php

declare(strict_types=1);

namespace App\Controller;

use App\Manager\FileCollectionManager;
use App\Manager\UploadManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class UploadController extends AbstractController
{
    #[Route('/api/upload/{name?}', name: 'upload_action', requirements: ["name" => "^[a-f0-9]{32}$"], methods: 'POST')]
    public function uploadAction(?string $name = null): JsonResponse
    {
        try {
            return $this->upload($name);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ]);
        }
    }

    private function upload(?string $name = ''): JsonResponse
    {
        $this->checkCredentials();

        $fileCollection = $this->get(FileCollectionManager::class)->getOrCreate($name);

        $newFiles = $this->get(UploadManager::class)->upload(
            $fileCollection
        );

        return new JsonResponse([
            'status' => 'ok',
            'collection_name' => $fileCollection->getName(),
            'files' => $newFiles,
        ]);
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
            FileCollectionManager::class,
            UploadManager::class,
        ]);
    }
}
