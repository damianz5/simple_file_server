<?php

namespace App\Manager;

use App\Model\FileCollection;
use App\Exception\WrongCollectionNameException;
use App\Exception\MissingCollectionDirectoryException;
use Symfony\Component\Finder\Finder;

class FileCollectionManager
{
    private $uploadDirectory;
    private $fileCollectionPrefix;

    public function __construct($uploadDirectory, $fileCollectionPrefix)
    {
        $this->uploadDirectory = $uploadDirectory;
        $this->fileCollectionPrefix = $fileCollectionPrefix;
    }

    /**
     * List files in requested FileCollection.
     *
     * @param string $name File Collection name
     * @return array
     */
    public function listFiles($name)
    {
        $fileCollection = $this->get($name);

        $finder = new Finder();
        $finder->files()->in($fileCollection->getDirectory());

        $files = [];

        foreach ($finder as $file) {
            $files[] = $fileCollection->getDirectory() . $file->getRelativePathname();
        }

        return $files;
    }

    /**
     * Get FileCollection if exists or create new based on provided $name.
     *
     * @param string $name
     * @return FileCollection
     * @throws \Exception when provided $name is wrong.
     */
    public function getOrCreate($name)
    {
        if (empty($name)) {
            $name = $this->generateName();
        }

        $this->validateName($name);

        if (!$this->exists($name)) {
            mkdir($this->getDirectory($name));
        }

        return $this->get($name);
    }

    /**
     * Get FileCollection based on provided $name.
     *
     * @param string $name
     * @return FileCollection
     * @throws MissingCollectionDirectoryException when FileCollection doesn't exists.
     */
    private function get($name)
    {
        $this->validateName($name);

        if (!$this->exists($name)) {
            throw new MissingCollectionDirectoryException('File collection directory does not exists!');
        }

        return new FileCollection(
            $name,
            $this->getDirectory($name)
        );
    }

    /**
     * Check if FileCollection exists based on provided $name.
     *
     * @param string $name
     * @return bool
     */
    private function exists($name)
    {
        $directory = $this->getDirectory($name);

        if (file_exists($directory) && is_dir($directory)) {
            return true;
        }

        return false;
    }

    /**
     * Generate unique name for FileCollection.
     *
     * @return string
     */
    private function generateName()
    {
        return md5(microtime() . uniqid(rand(), true) . 'salt');
    }

    /**
     * Validate provided $name.
     *
     * @param string $name
     * @return void
     * @throws WrongCollectionNameException when $name is wrong.
     */
    private function validateName($name)
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $name)) {
            throw new WrongCollectionNameException('Wrong File collection directory name!');
        }
    }

    /**
     * Returns FileCollection directory.
     *
     * @param string $name
     * @return string
     */
    private function getDirectory($name)
    {
        return sprintf(
            "%s%s%s/",
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $name
        );
    }
}
