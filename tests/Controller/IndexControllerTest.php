<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    public function testListAction(): void
    {
        $fileCollectionName = '195267c9b7f2daaa13e6b43351008d86';

        $client = static::createClient();

        $client->request(
            'GET',
            '/api/list/' . $fileCollectionName,
            [],
            [],
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode(
            $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR
        );

        self::assertResponseIsSuccessful();

        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($fileCollectionName, $response['collection_name']);
        $this->assertCount(3, $response['files']);
        $this->assertContains(
            'tests/Fixtures/test-195267c9b7f2daaa13e6b43351008d86/96218da4-test1-copy.html',
            $response['files']
        );
        $this->assertContains(
            'tests/Fixtures/test-195267c9b7f2daaa13e6b43351008d86/2aebb411-test1-copy.png',
            $response['files']
        );
        $this->assertContains(
            'tests/Fixtures/test-195267c9b7f2daaa13e6b43351008d86/46b8eac3-test1-copy.txt',
            $response['files']
        );
    }

    public function testListWrongCredentialsAction(): void
    {
        $fileCollectionName = '195267c9b7f2daaa13e6b43351008d86';

        $client = static::createClient();

        $client->request(
            'GET',
            '/api/list/' . $fileCollectionName,
            [],
            [],
            ['HTTP_AUTHKEY' => 'WRONG']
        );

        $response = json_decode(
            $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR
        );

        $this->assertEquals('error', $response['status']);
    }
}
