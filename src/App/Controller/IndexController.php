<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class IndexController extends Controller
{
    use CredentialsCheckTrait;

    /**
     * @Route("/api/list/{name}", requirements={"name" = "^[a-f0-9]{32}$"})
     * @Method({"GET"})
     * @param $name string
     * @return JsonResponse
     */
    public function listAction($name)
    {
        try {
            $this->checkCredentials();
            $files = $this->get('file_collection_manager')->listFiles($name);

            return new JsonResponse(array(
                'status' => 'ok',
                'collection_name' => $name,
                'files' => $files
            ));
        } catch (\Exception $e) {
            return new JsonResponse(array(
                'status' => 'error',
                'message' => $e->getMessage()
            ));
        }
    }
}
