<?php

namespace Tests\App\Validator;

use App\Validator\FileValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAllPossibleExtensionsValidate()
    {
        $fileValidator = new FileValidator();

        $pngFile = $this->getMockFile('png');
        $htmlFile = $this->getMockFile('html');
        $txtFile = $this->getMockFile('txt');


        $this->assertTrue($fileValidator->validate($pngFile));
        $this->assertTrue($fileValidator->validate($htmlFile));
        $this->assertTrue($fileValidator->validate($txtFile));
    }

    public function testUnsupportedFiles()
    {
        $fileValidator = new FileValidator();

        $phpFile = $this->getMockFile('php');

        $this->assertFalse($fileValidator->validate($phpFile));
    }


    private function getMockFile($suffix)
    {
        return new UploadedFile(__DIR__.'/../../Fixtures/test1.'.$suffix, 'foo.'.$suffix);
    }
}
