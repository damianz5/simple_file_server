<?php

declare(strict_types=1);

namespace App\Model;

class FileCollection
{
    public function __construct(private string $name, private string $directory) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getDirectory(): string
    {
        return $this->directory;
    }
}
