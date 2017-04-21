<?php

namespace App\Uploader;

use App\Model\FileCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * Moves UploadedFile to new location.
     *
     * @param UploadedFile   $file
     * @param FileCollection $fileCollection
     *
     * @return File
     */
    public function upload(UploadedFile $file, FileCollection $fileCollection)
    {
        return $file->move(
            $fileCollection->getDirectory(),
            $this->generateFileName($file)
        );
    }

    private function generateFileName(UploadedFile $file)
    {
        $baseFilename = str_replace('.'.$file->getClientOriginalExtension(), null, $file->getClientOriginalName());
        $baseFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $baseFilename);

        return $this->generateHash().'-'.$baseFilename.'.'.$file->guessExtension();
    }

    private function generateHash()
    {
        return substr(md5(microtime().uniqid(rand(), true).'salt'), 0, 8);
    }
}
