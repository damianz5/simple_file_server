<?php

declare(strict_types=1);

namespace App\Manager;

use App\Exception\MissingCollectionDirectoryException;
use App\Exception\WrongCollectionNameException;
use App\Model\FileCollection;
use JetBrains\PhpStorm\Pure;
use RuntimeException;
use Symfony\Component\Finder\Finder;

class FileCollectionManager
{
    public function __construct(
        private string $uploadDirectory,
        private string $fileCollectionPrefix
    ) {}

    /**
     * List files in requested FileCollection.
     */
    public function listFiles(string $name): array
    {
        $fileCollection = $this->get($name);

        $finder = new Finder();
        $finder->files()->in($fileCollection->getDirectory());

        $files = [];

        foreach ($finder as $file) {
            $files[] = $fileCollection->getDirectory().$file->getRelativePathname();
        }

        return $files;
    }

    /**
     * Get FileCollection if exists or create new based on provided $name.
     *
     * @throws \Exception when provided $name is wrong.
     */
    public function getOrCreate(?string $name = ''): FileCollection
    {
        if (empty($name)) {
            $name = $this->generateName();
        }

        $this->validateName($name);

        if (!$this->exists($name) && !mkdir($concurrentDirectory = $this->getDirectory($name)) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        return $this->get($name);
    }

    /**
     * Get FileCollection based on provided $name.
     *
     * @throws MissingCollectionDirectoryException|\App\Exception\WrongCollectionNameException when FileCollection doesn't exists.
     */
    private function get(string $name): FileCollection
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
     */
    #[Pure] private function exists(string $name): bool
    {
        $directory = $this->getDirectory($name);

        return file_exists($directory) && is_dir($directory);
    }

    /**
     * Generate unique name for FileCollection.
     */
    private function generateName(): string
    {
        return md5(microtime().uniqid((string) mt_rand(), true).'salt');
    }

    /**
     * Validate provided $name.
     *
     * @throws WrongCollectionNameException when $name is wrong.
     */
    private function validateName(string $name): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/i', $name)) {
            throw new WrongCollectionNameException('Wrong File collection directory name!');
        }
    }

    /**
     * Returns FileCollection directory.
     */
    private function getDirectory(string $name): string
    {
        return sprintf(
            '%s%s%s/',
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $name
        );
    }
}
