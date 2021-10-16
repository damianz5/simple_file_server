<?php

declare(strict_types=1);

namespace App\Tests\Validator;

use App\Validator\FileValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidatorTest extends TestCase
{
    public function testAllPossibleExtensionsValidate(): void
    {
        $fileValidator = new FileValidator();

        $pngFile = $this->getMockFile('png');
        $htmlFile = $this->getMockFile('html');
        $txtFile = $this->getMockFile('txt');

        $this->assertTrue($fileValidator->validate($pngFile));
        $this->assertTrue($fileValidator->validate($htmlFile));
        $this->assertTrue($fileValidator->validate($txtFile));
    }

    public function testUnsupportedFiles(): void
    {
        $fileValidator = new FileValidator();

        $phpFile = $this->getMockFile('php');

        $this->assertFalse($fileValidator->validate($phpFile));
    }

    private function getMockFile($suffix): UploadedFile
    {
        return new UploadedFile(__DIR__.'/../Fixtures/test1.'.$suffix, 'foo.'.$suffix);
    }
}
