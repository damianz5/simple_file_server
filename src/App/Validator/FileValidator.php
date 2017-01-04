<?php

namespace App\Validator;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileValidator
{
    public function validate(UploadedFile $file)
    {
        if (!in_array($file->guessExtension(), array('png', 'html', 'txt'))) {
            return false;
        }

        return true;
    }
}
