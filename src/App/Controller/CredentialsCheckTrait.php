<?php

namespace App\Controller;

use RuntimeException;

trait CredentialsCheckTrait
{
    private function checkCredentials()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $accessList = $this->getParameter('access_list');
        $key = sprintf('HTTP_%s', $this->getParameter('credentials_header_name'));

        if (!$request->server->has($key)
            || !in_array($request->server->get($key), $accessList)
        ) {
            throw new RuntimeException('Unauthorized!');
        }
    }

    abstract public function getParameter($key);

    abstract public function get($key);
}
