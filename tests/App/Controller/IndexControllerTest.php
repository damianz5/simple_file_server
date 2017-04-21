<?php

namespace Tests\Client;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class IndexControllerTest extends WebTestCase
{
    /** @var Client */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();
    }

    public function testListAction()
    {
        $fileCollectionName = '195267c9b7f2daaa13e6b43351008d86';

        $this->client->request(
            'GET',
            '/api/list/'.$fileCollectionName,
            [],
            [],
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($fileCollectionName, $response['collection_name']);
        $this->assertCount(3, $response['files']);
        $this->assertContains(
            'tests/Fixtures/example-195267c9b7f2daaa13e6b43351008d86/95fcf13b-test1.html',
            $response['files']
        );
        $this->assertContains(
            'tests/Fixtures/example-195267c9b7f2daaa13e6b43351008d86/203f8040-test1.png',
            $response['files']
        );
        $this->assertContains(
            'tests/Fixtures/example-195267c9b7f2daaa13e6b43351008d86/4d6f9524-test1.txt',
            $response['files']
        );
    }

    public function testListActionWithInvalidCredentials()
    {
        $this->client->request(
            'GET',
            '/api/list/195267c9b7f2daaa13e6b43351008d86',
            [],
            [],
            ['HTTP_AUTHKEY' => 'some-invalid-key']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Unauthorized!', $response['message']);
    }

    public function testListActionWithInvalidFileCollectionName()
    {
        $this->client->request(
            'GET',
            '/api/list/12345678901234567890123456789012',
            [],
            [],
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('File collection directory does not exists!', $response['message']);
    }
}
