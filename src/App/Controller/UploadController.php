<?php

namespace App\Controller;

use App\Model\FileCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadController extends Controller
{
    use CredentialsCheckTrait;

    /**
     * @Route("/api/upload/{name}", defaults={"name" = null}, requirements={"name" = "^[a-f0-9]{32}$"})
     * @Method({"POST"})
     * @param string|null $name
     * @return JsonResponse
     */
    public function uploadAction($name = null)
    {
        try {
            $this->checkCredentials();

            /** @var $fileCollection FileCollection */
            $fileCollection = $this->get('file_collection_manager')->getOrCreate($name);

            $newFiles = $this->get('upload_manager')->upload(
                $fileCollection
            );

            return new JsonResponse(array(
                'status' => 'ok',
                'collection_name' => $fileCollection->getName(),
                'files' => $newFiles
            ));
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'status' => 'error',
                'message' => $e->getMessage()
            ));
        }
    }

}
