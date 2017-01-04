<?php

namespace Tests\App\Manager;

use App\Manager\UploadManager;
use App\Model\FileCollection;
use App\Uploader\FileUploader;
use App\Validator\FileValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class UploadManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $uploadDirectory;

    /** @var string */
    private $fileCollectionPrefix;

    /** @var string */
    private $fileCollectionName;

    /** @var string */
    private $location;

    public function setUp()
    {
        $this->uploadDirectory = 'tests/Fixtures/';
        $this->fileCollectionPrefix = 'test-';
        $this->fileCollectionName = '7d97656c1b4da64e278be1f224b37986';

        $this->location = sprintf("%s%s%s/",
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $this->fileCollectionName
        );

        copy($this->uploadDirectory . 'test1.html', $this->location . 'test1.html');
        copy($this->uploadDirectory . 'test1.txt', $this->location . 'test1.txt');
        copy($this->uploadDirectory . 'test1.png', $this->location . 'test1.png');
    }

    public function tearDown()
    {
        @unlink($this->location . 'test1.html');
        @unlink($this->location . 'test1.txt');
        @unlink($this->location . 'test1.png');
    }

    public function testUploadManager()
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

    public function testUploadWithUnsupportedFileTypeManager()
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

    public function testUploadWithNotSupportedFilesAsArray()
    {
        $files = [
            0 => [
                $this->getMockFile('png'),
                $this->getMockFile('html'),
                $this->getMockFile('txt'),
            ]
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

    private function getMockForRequestStack(array $files)
    {
        $request = Request::create('/', 'POST', array(), array(), $files);

        $request->setSession(new Session(new MockArraySessionStorage()));
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return $requestStack;
    }

    private function getMockFile($suffix)
    {
        $file = $this->location . 'test1.' .$suffix;

        return new UploadedFile(
            $file,
            'test1.'.$suffix,
            mime_content_type($file),
            filesize($file),
            UPLOAD_ERR_OK,
            true
        );
    }

    private function getMockUnsupportedFile($filename)
    {
        $file = sprintf("%s%s",
            $this->uploadDirectory,
            $filename
        );

        return new UploadedFile(
            $file,
            $filename,
            mime_content_type($file),
            filesize($file),
            UPLOAD_ERR_OK,
            true
        );
    }

    private function getMockFileCollection()
    {
        return new FileCollection($this->fileCollectionName, $this->location);
    }
}
