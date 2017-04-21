<?php

namespace App\Controller;

use App\Model\FileCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadController extends Controller
{
    use CredentialsCheckTrait;

    /**
     * @Route("/api/upload/{name}", defaults={"name" = null}, requirements={"name" = "^[a-f0-9]{32}$"})
     * @Method({"POST"})
     *
     * @param string|null $name
     *
     * @return JsonResponse
     */
    public function uploadAction($name = null)
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

    private function upload($name)
    {
        $this->checkCredentials();

        /** @var $fileCollection FileCollection */
        $fileCollection = $this->get('app.file_collection_manager')->getOrCreate($name);

        $newFiles = $this->get('app.upload_manager')->upload(
            $fileCollection
        );

        return new JsonResponse([
            'status'          => 'ok',
            'collection_name' => $fileCollection->getName(),
            'files'           => $newFiles,
        ]);
    }
}
