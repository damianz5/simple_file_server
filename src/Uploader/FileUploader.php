<?php

declare(strict_types=1);

namespace App\Uploader;

use App\Model\FileCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    /**
     * Moves UploadedFile to new location.
     */
    public function upload(UploadedFile $file, FileCollection $fileCollection): File
    {
        return $file->move(
            $fileCollection->getDirectory(),
            $this->generateFileName($file)
        );
    }

    private function generateFileName(UploadedFile $file): string
    {
        $baseFilename = str_replace('.'.$file->getClientOriginalExtension(), '', $file->getClientOriginalName());
        $baseFilename = preg_replace('/[^a-zA-Z0-9_-]/', '', $baseFilename);

        return $this->generateHash().'-'.$baseFilename.'.'.$file->guessExtension();
    }

    private function generateHash(): string
    {
        return substr(md5(microtime().uniqid((string) mt_rand(), true).'salt'), 0, 8);
    }
}
