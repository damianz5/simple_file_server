<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadControllerTest extends WebTestCase
{
    public function setUp(): void
    {
        !file_exists($file = 'tests/Fixtures/test1-copy.html') && copy('tests/Fixtures/test1.html', $file);
        !file_exists($file = 'tests/Fixtures/test1-copy.png') && copy('tests/Fixtures/test1.png', $file);
        !file_exists($file = 'tests/Fixtures/test1-copy.txt') && copy('tests/Fixtures/test1.txt', $file);

        parent::setUp();
    }

    public function tearDown(): void
    {
        file_exists($file = 'tests/Fixtures/test1-copy.html') && unlink($file);
        file_exists($file = 'tests/Fixtures/test1-copy.png') && unlink($file);
        file_exists($file = 'tests/Fixtures/test1-copy.txt') && unlink($file);

        parent::tearDown();
    }

    public function testUploadToNewFileCollectionAction(): void
    {
        $client = static::createClient();

        $files = [
            $this->getMockFile('test1-copy.html', 'tests/Fixtures/test1-copy.html'),
            $this->getMockFile('test1-copy.png', 'tests/Fixtures/test1-copy.png'),
            $this->getMockFile('test1-copy.txt', 'tests/Fixtures/test1-copy.txt'),
        ];

        $client->request(
            'POST',
            '/api/upload',
            [],
            $files,
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode(
            $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertArrayHasKey('collection_name', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('files', $response);
        $this->assertEquals('ok', $response['status']);
        $this->assertCount(3, $response['files']);

        foreach ($response['files'] as $file) {
            $this->assertFileExists($file);
        }

        $this->cleanupFiles($response['files'], $response['collection_name']);
    }

    public function testUploadToExistFileCollectionAction(): void
    {
        $client = static::createClient();
        $fileSystem = new Filesystem();

        $collectionName = '7d97656c1b4da64e278be1f224b37986';

        $fileSystem->mirror(
            'tests/Fixtures/test-195267c9b7f2daaa13e6b43351008d86',
            'tests/Fixtures/test-' . $collectionName
        );

        $files = [
            $this->getMockFile('test1-copy.png', 'tests/Fixtures/test1-copy.png'),
        ];

        $client->request(
            'POST',
            '/api/upload/'.$collectionName,
            [],
            $files,
            ['HTTP_AUTHKEY' => 'code-for-tests']
        );

        $response = json_decode(
            $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertArrayHasKey('collection_name', $response);
        $this->assertArrayHasKey('status', $response);
        $this->assertArrayHasKey('files', $response);
        $this->assertEquals('ok', $response['status']);
        $this->assertEquals($collectionName, $response['collection_name'], 'FileCollection name should not change');

        $this->assertCount(1, $response['files']);

        foreach ($response['files'] as $file) {
            $this->assertFileExists($file);
        }

        $fileSystem->remove('tests/Fixtures/test-' . $collectionName);
    }

    public function testUploadActionWithInvalidCredentials(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/upload',
            [],
            [],
            ['HTTP_AUTHKEY' => 'some-invalid-key']
        );

        $response = json_decode(
            $client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Unauthorized!', $response['message']);
    }

    private function cleanupFiles($filesList, $collectionName): void
    {
        $fileSystem = new Filesystem();

        foreach ($filesList as $file) {
            $fileSystem->remove($file);
        }

        $fileCollectionDirectory = "tests/Fixtures/test-{$collectionName}";

        $fileSystem->remove($fileCollectionDirectory);
    }

    private function getMockFile($filename, $file): UploadedFile
    {
        return new UploadedFile(
            $file,
            $filename,
            mime_content_type($file),
            null,
            true
        );
    }
}
