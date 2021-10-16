<?php

declare(strict_types=1);

namespace App\Tests\Manager;

use App\Exception\MissingCollectionDirectoryException;
use App\Exception\WrongCollectionNameException;
use App\Manager\FileCollectionManager;
use PHPUnit\Framework\TestCase;
use App\Model\FileCollection;

class FileCollectionManagerTest extends TestCase
{
    private string $uploadDirectory;

    private string $fileCollectionPrefix;

    private string $fileCollectionName;

    private string $location;

    private FileCollectionManager $manager;

    public function setUp(): void
    {
        $this->uploadDirectory = 'tests/Fixtures/';
        $this->fileCollectionPrefix = 'test-';
        $this->fileCollectionName = '7d97656c1b4da64e278be1f224b37986';

        $this->location = sprintf('%s%s%s/',
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $this->fileCollectionName
        );

        !file_exists($this->location) && mkdir($this->location);

        !file_exists($file = $this->location . '1-test1.png') && copy($this->uploadDirectory . 'test1.png', $file);
        !file_exists($file = $this->location . '2-test1.html') && copy($this->uploadDirectory . 'test1.html', $file);
        !file_exists($file = $this->location . '3-test1.txt') && copy($this->uploadDirectory . 'test1.txt', $file);

        $this->manager = new FileCollectionManager(
            $this->uploadDirectory,
            $this->fileCollectionPrefix
        );

        parent::setUp();
    }

    public function tearDown(): void
    {
        file_exists($file = $this->location . '1-test1.png') && unlink($file);
        file_exists($file = $this->location . '2-test1.html') && unlink($file);
        file_exists($file = $this->location . '3-test1.txt') && unlink($file);

        file_exists($this->location) && is_dir($this->location) && rmdir($this->location);

        parent::tearDown();
    }

    public function testListFiles(): void
    {
        $files = $this->manager->listFiles($this->fileCollectionName);

        $this->assertCount(3, $files);

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    public function testFailOnWrongCollectionName(): void
    {
        $this->expectExceptionMessage("Wrong File collection directory name!");
        $this->expectException(WrongCollectionNameException::class);

        $fileCollectionName = 'wrong-name';

        $manager = new FileCollectionManager(
            $this->uploadDirectory,
            $this->fileCollectionPrefix
        );

        $manager->listFiles($fileCollectionName);
    }

    public function testFailOnUnexistentCollection(): void
    {
        $this->expectExceptionMessage("File collection directory does not exists!");
        $this->expectException(MissingCollectionDirectoryException::class);

        $fileCollectionName = '12345678901234567890123456789012';

        $manager = new FileCollectionManager(
            $this->uploadDirectory,
            $this->fileCollectionPrefix
        );

        $manager->listFiles($fileCollectionName);
    }

    public function testGetOrCreateUsingValidName(): void
    {
        $fileCollection = $this->manager->getOrCreate($this->fileCollectionName);

        $this->assertEquals($this->fileCollectionName, $fileCollection->getName());
        $this->assertEquals($this->location, $fileCollection->getDirectory());
    }

    public function testGetOrCreateUsingEmptyName(): void
    {
        $fileCollection = $this->manager->getOrCreate('');

        $this->assertEquals(32, strlen($fileCollection->getName()));
        $this->assertDirectoryExists($fileCollection->getDirectory());
        $this->assertDirectoryIsReadable($fileCollection->getDirectory());

        rmdir($fileCollection->getDirectory());
    }

    public function testGetOrCreateUsingInvalidName(): void
    {
        $this->expectExceptionMessage("Wrong File collection directory name!");
        $this->expectException(WrongCollectionNameException::class);

        $this->manager->getOrCreate('invalid-name');
    }

    public function testGetOrCreateUsingValidNameWithUnexistingDirectory(): void
    {
        $validName = md5('some-random-string');

        $location = sprintf('%s%s%s/',
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $validName
        );

        $this->assertFalse(file_exists($location) && is_dir($location), 'FileCollection directory should not exists');

        $fileCollection = $this->manager->getOrCreate($validName);

        $this->assertTrue(file_exists($location) && is_dir($location), 'FileCollection directory should exists');
        $this->assertEquals(32, strlen($fileCollection->getName()));
        $this->assertDirectoryExists($fileCollection->getDirectory());
        $this->assertDirectoryIsReadable($fileCollection->getDirectory());

        @rmdir($fileCollection->getDirectory());
    }
}
