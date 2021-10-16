<?php

declare(strict_types=1);

namespace App\Tests\Uploader;

use App\Model\FileCollection;
use App\Uploader\FileUploader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploaderTest extends TestCase
{
    public function testUpload(): void
    {
        $targetPath = __DIR__.'/../../Fixtures/directory/test1_copy.png';
        $uploadedFile = $this->getMockPngFile($targetPath);

        $targetDir = __DIR__.'/../../Fixtures/directory/';
        $fileCollection = $this->getMockFileCollection('files1', $targetDir);

        $fileUploader = new FileUploader();
        $newFile = $fileUploader->upload($uploadedFile, $fileCollection);

        $this->assertFileExists($newFile->getPathname());

        file_exists($newFile->getPathname()) && unlink($newFile->getPathname());
    }

    private function getMockPngFile(string $targetPath): UploadedFile
    {
        $path = __DIR__ . '/../Fixtures/test1_copy.png';
        file_exists($path) && unlink($path);
        copy(__DIR__ . '/../Fixtures/test1.png', $path);

        file_exists($targetPath) && unlink($targetPath);

        return new UploadedFile(
            $path,
            'test1.png',
            'image/png',
            UPLOAD_ERR_OK,
            true
        );
    }

    private function getMockFileCollection(string $name, string $dir): FileCollection
    {
        return new FileCollection($name, $dir);
    }
}
