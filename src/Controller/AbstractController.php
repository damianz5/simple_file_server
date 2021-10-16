<?php

declare(strict_types=1);

namespace App\Controller;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Symfony\Component\HttpFoundation\Request;

class AbstractController extends BaseAbstractController
{
    protected function checkCredentials(): void
    {
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();
        $accessList = $this->getParameter('app.access_list');
        $key = sprintf('HTTP_%s', $this->getParameter('app.credentials_header_name'));

        if (!$request->server->has($key)
            || !in_array($request->server->get($key), $accessList, true)
        ) {
            throw new RuntimeException('Unauthorized!');
        }
    }
}
