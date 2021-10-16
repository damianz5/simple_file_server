<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidator
{
    public function validate(UploadedFile $file): bool
    {
        if (!in_array($file->guessExtension(), ['png', 'html', 'txt'])) {
            return false;
        }

        return true;
    }
}
