<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Manager\UploadManager;
use App\Model\FileCollection;
use App\Uploader\FileUploader;
use App\Validator\FileValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UploadManagerTest extends TestCase
{
    private string $uploadDirectory;

    private string $fileCollectionPrefix;

    private string $fileCollectionName;

    private string $location;

    public function setUp(): void
    {
        $this->uploadDirectory = 'tests/Fixtures/';
        $this->fileCollectionPrefix = 'test-';
        $this->fileCollectionName = '7d97656c1b4da64e278be1f224b37986';

        $this->location = sprintf('%s%s%s/',
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $this->fileCollectionName
        );

        !file_exists($this->location) && mkdir($this->location);

        !file_exists($file = $this->location . 'test1.png') && copy($this->uploadDirectory . 'test1.png', $file);
        !file_exists($file = $this->location . 'test1.html') && copy($this->uploadDirectory . 'test1.html', $file);
        !file_exists($file = $this->location . 'test1.txt') && copy($this->uploadDirectory . 'test1.txt', $file);

        parent::setUp();
    }

    public function tearDown(): void
    {
        file_exists($file = $this->location . 'test1.png') && unlink($file);
        file_exists($file = $this->location . 'test1.html') && unlink($file);
        file_exists($file = $this->location . 'test1.txt') && unlink($file);

        file_exists($this->location) && is_dir($this->location) && rmdir($this->location);

        parent::tearDown();
    }

    public function testUploadManager(): void
    {
        $files = [
            $this->getMockFile('png'),
            $this->getMockFile('html'),
            $this->getMockFile('txt'),
        ];

        $requestStack = $this->getMockForRequestStack($files);
        $fileCollection = $this->getMockFileCollection();

        $uploadManager = new UploadManager(
            new FileUploader(),
            new FileValidator(),
            $requestStack
        );

        $result = $uploadManager->upload($fileCollection);

        $this->assertCount(3, $result);

        foreach ($result as $file) {
            $this->assertFileExists($file);
        }

        foreach ($result as $file) {
            @unlink($file);
        }
    }

    public function testUploadWithUnsupportedFileTypeManager(): void
    {
        $files = [
            $this->getMockFile('png'),
            $this->getMockUnsupportedFile('test1.php'),
            $this->getMockFile('txt'),
        ];

        $requestStack = $this->getMockForRequestStack($files);
        $fileCollection = $this->getMockFileCollection();

        $uploadManager = new UploadManager(
            new FileUploader(),
            new FileValidator(),
            $requestStack
        );

        $result = $uploadManager->upload($fileCollection);

        $this->assertCount(2, $result);

        foreach ($result as $file) {
            $this->assertFalse(strpos('test1.php', $file));
            $this->assertFileExists($file);
        }

        foreach ($result as $file) {
            @unlink($file);
        }
    }

    public function testUploadWithNotSupportedFilesAsArray(): void
    {
        $files = [
            0 => [
                $this->getMockFile('png'),
                $this->getMockFile('html'),
                $this->getMockFile('txt'),
            ],
        ];

        $requestStack = $this->getMockForRequestStack($files);
        $fileCollection = $this->getMockFileCollection();

        $uploadManager = new UploadManager(
            new FileUploader(),
            new FileValidator(),
            $requestStack
        );

        $result = $uploadManager->upload($fileCollection);

        $this->assertCount(0, $result);
    }

    private function getMockForRequestStack(array $files): RequestStack
    {
        $request = Request::create('/', 'POST', [], [], $files);

        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }

    private function getMockFile($suffix): UploadedFile
    {
        $file = $this->location.'test1.'.$suffix;

        return new UploadedFile(
            $file,
            'test1.'.$suffix,
            mime_content_type($file),
            UPLOAD_ERR_OK,
            true
        );
    }

    private function getMockUnsupportedFile(string $filename): UploadedFile
    {
        $file = sprintf('%s%s',
            $this->uploadDirectory,
            $filename
        );

        return new UploadedFile(
            $file,
            $filename,
            mime_content_type($file),
            UPLOAD_ERR_OK,
            true
        );
    }

    private function getMockFileCollection(): FileCollection
    {
        return new FileCollection($this->fileCollectionName, $this->location);
    }
}
