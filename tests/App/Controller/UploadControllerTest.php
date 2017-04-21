<?php

namespace Tests\Client;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadControllerTest extends WebTestCase
{
    /** @var Client */
    private $client = null;

    public function setUp()
    {
        $this->client = static::createClient();

        copy('tests/Fixtures/test1.html', 'tests/Fixtures/test1-copy.html');
        copy('tests/Fixtures/test1.png', 'tests/Fixtures/test1-copy.png');
        copy('tests/Fixtures/test1.txt', 'tests/Fixtures/test1-copy.txt');
    }

    public function tearDown()
    {
        @unlink('tests/Fixtures/test1-copy.html');
        @unlink('tests/Fixtures/test1-copy.png');
        @unlink('tests/Fixtures/test1-copy.txt');
    }

    public function testUploadToNewFileCollectionAction()
    {
        $files = [
            $this->getMockFile('test1-copy.html', 'tests/Fixtures/test1-copy.html'),
            $this->getMockFile('test1-copy.png', 'tests/Fixtures/test1-copy.png'),
            $this->getMockFile('test1-copy.txt', 'tests/Fixtures/test1-copy.txt'),
        ];

        $this->client->request(
            'POST',
            '/api/upload',
            [],
            $files,
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $collectionName = $response['collection_name'];

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertArrayHasKey('collection_name', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('files', $response);
        $this->assertEquals('ok', $response['status']);
        $this->assertCount(3, $response['files']);

        foreach ($response['files'] as $file) {
            $this->assertFileExists($file);
        }

        return $collectionName;
    }

    /**
     * @depends testUploadToNewFileCollectionAction
     */
    public function testUploadToExistFileCollectionAction($collectionName)
    {
        $files = [
            $this->getMockFile('test1-copy.png', 'tests/Fixtures/test1-copy.png'),
        ];

        $this->client->request(
            'POST',
            '/api/upload/'.$collectionName,
            [],
            $files,
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertArrayHasKey('collection_name', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('files', $response);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($collectionName, $response['collection_name'], 'FileCollection name should not change');

        $this->assertCount(1, $response['files']);

        foreach ($response['files'] as $file) {
            $this->assertFileExists($file);
        }

        return $collectionName;
    }

    /**
     * @depends testUploadToNewFileCollectionAction
     */
    public function testVeirfyUploadedFiles($collectionName)
    {
        $this->client->request(
            'GET',
            '/api/list/'.$collectionName,
            [],
            [],
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertArrayHasKey('collection_name', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('files', $response);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($collectionName, $response['collection_name'], 'FileCollection name should not change');

        $this->assertCount(4, $response['files']);

        foreach ($response['files'] as $file) {
            $this->assertFileExists($file);
        }

        $this->cleanupFiles($response['files'], $collectionName);
    }

    public function testUploadActionWithInvalidCredentials()
    {
        $this->client->request(
            'POST',
            '/api/upload',
            [],
            [],
            ['HTTP_AUTHKEY' => 'some-invalid-key']
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Unauthorized!', $response['message']);
    }

    private function cleanupFiles($filesList, $collectionName)
    {
        foreach ($filesList as $file) {
            @unlink($file);
        }

        $fileCollectionDirectory = "tests/Fixtures/example-{$collectionName}";

        if (file_exists($fileCollectionDirectory) and is_dir($fileCollectionDirectory)) {
            @rmdir($fileCollectionDirectory);
        }
    }

    private function getMockFile($filename, $file)
    {
        return new UploadedFile(
            $file,
            $filename,
            mime_content_type($file),
            filesize($file),
            UPLOAD_ERR_OK,
            true
        );
    }
}
