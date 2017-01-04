<?php

namespace Tests\App\Uploader;

use App\Model\FileCollection;
use App\Uploader\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends \PHPUnit_Framework_TestCase
{
    public function testUpload()
    {
        $targetPath = __DIR__.'/../../Fixtures/directory/test1_copy.png';
        $uploadedFile = $this->getMockPngFile($targetPath);

        $targetDir = __DIR__.'/../../Fixtures/directory/';
        $fileCollection = $this->getMockFileCollection('files1', $targetDir);

        $fileUploader = new FileUploader();
        $newFile = $fileUploader->upload($uploadedFile, $fileCollection);

        $this->assertFileExists($newFile->getPathname());

        @unlink($newFile->getPathname());
    }

    private function getMockPngFile($targetPath)
    {
        $path = __DIR__.'/../../Fixtures/test1_copy.png';
        @unlink($path);
        @unlink($targetPath);
        copy(__DIR__.'/../../Fixtures/test1.png', $path);

        return new UploadedFile(
            $path,
            'test1.png',
            'image/png',
            filesize($path),
            UPLOAD_ERR_OK,
            true
        );
    }

    private function getMockFileCollection($name, $dir)
    {
        return new FileCollection($name, $dir);
    }
}
