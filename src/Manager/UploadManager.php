<?php

declare(strict_types=1);

namespace App\Manager;

use App\Model\FileCollection;
use App\Uploader\FileUploader;
use App\Validator\FileValidator;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

class UploadManager
{
    public function __construct(
        private FileUploader $fileUploader,
        private FileValidator $fileValidator,
        private RequestStack $requestStack
    ) {}

    public function upload(FileCollection $fileCollection): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $newFiles = [];

        Assert::notNull($request);

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
