<?php

namespace App\Manager;

use App\Model\FileCollection;
use App\Uploader\FileUploader;
use App\Validator\FileValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadManager
{
    private $fileUploader;
    private $fileValidator;
    private $requestStack;

    public function __construct(FileUploader $fileUploader, FileValidator $fileValidator, RequestStack $requestStack)
    {
        $this->fileUploader = $fileUploader;
        $this->fileValidator = $fileValidator;
        $this->requestStack = $requestStack;
    }

    public function upload(FileCollection $fileCollection)
    {
        $request = $this->requestStack->getCurrentRequest();
        $newFiles = [];

        foreach ($request->files->all() as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            if (!$this->fileValidator->validate($file)) {
                continue;
            }

            $newFile = $this->fileUploader->upload($file, $fileCollection);
            $newFiles[] = $newFile->getPath().'/'.$newFile->getFilename();
        }

        return $newFiles;
    }
}
