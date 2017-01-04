<?php

namespace App\Model;

class FileCollection
{
    /** @var string */
    private $name;

    /** @var string */
    private $directory;

    public function __construct($name, $directory)
    {
        $this->name = $name;
        $this->directory = $directory;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }
}
