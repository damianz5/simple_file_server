<?php

namespace Tests\App\Manager;

use App\Manager\FileCollectionManager;

class FileCollectionManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var string */
    private $uploadDirectory;

    /** @var string */
    private $fileCollectionPrefix;

    /** @var string */
    private $fileCollectionName;

    /** @var string */
    private $location;

    /** @var FileCollectionManager */
    private $manager;

    public function setUp()
    {
        $this->uploadDirectory = 'tests/Fixtures/';
        $this->fileCollectionPrefix = 'test-';
        $this->fileCollectionName = '7d97656c1b4da64e278be1f224b37986';

        $this->location = sprintf('%s%s%s/',
            $this->uploadDirectory,
            $this->fileCollectionPrefix,
            $this->fileCollectionName
        );

        copy($this->uploadDirectory.'test1.png', $this->location.'1-test1.png');
        copy($this->uploadDirectory.'test1.html', $this->location.'2-test1.html');
        copy($this->uploadDirectory.'test1.txt', $this->location.'3-test1.txt');

        $this->manager = new FileCollectionManager(
            $this->uploadDirectory,
            $this->fileCollectionPrefix
        );
    }

    public function tearDown()
    {
        @unlink($this->location.'1-test1.png');
        @unlink($this->location.'2-test1.html');
        @unlink($this->location.'3-test1.txt');
    }

    public function testListFiles()
    {
        $files = $this->manager->listFiles($this->fileCollectionName);

        $this->assertCount(3, $files);

        foreach ($files as $file) {
            $this->assertFileExists($file);
        }
    }

    /**
     * @expectedException \App\Exception\WrongCollectionNameException
     * @expectedExceptionMessage Wrong File collection directory name!
     */
    public function testFailOnWrongCollectionName()
    {
        $fileCollectionName = 'wrong-name';

        $manager = new FileCollectionManager(
            $this->uploadDirectory,
            $this->fileCollectionPrefix
        );

        $manager->listFiles($fileCollectionName);
    }

    /**
     * @expectedException \App\Exception\MissingCollectionDirectoryException
     * @expectedExceptionMessage File collection directory does not exists!
     */
    public function testFailOnUnexistentCollection()
    {
        $fileCollectionName = '12345678901234567890123456789012';

        $manager = new FileCollectionManager(
            $this->uploadDirectory,
            $this->fileCollectionPrefix
        );

        $manager->listFiles($fileCollectionName);
    }

    public function testGetOrCreateUsingValidName()
    {
        $fileCollection = $this->manager->getOrCreate($this->fileCollectionName);

        $this->assertInstanceOf('App\Model\FileCollection', $fileCollection);

        $this->assertEquals($this->fileCollectionName, $fileCollection->getName());
        $this->assertEquals($this->location, $fileCollection->getDirectory());
    }

    public function testGetOrCreateUsingEmptyName()
    {
        $fileCollection = $this->manager->getOrCreate('');

        $this->assertInstanceOf('App\Model\FileCollection', $fileCollection);
        $this->assertEquals(32, strlen($fileCollection->getName()));
        $this->assertDirectoryExists($fileCollection->getDirectory());
        $this->assertDirectoryIsReadable($fileCollection->getDirectory());

        @rmdir($fileCollection->getDirectory());
    }

    /**
     * @expectedException \App\Exception\WrongCollectionNameException
     * @expectedExceptionMessage Wrong File collection directory name!
     */
    public function testGetOrCreateUsingInvalidName()
    {
        $this->manager->getOrCreate('invalid-name');
    }

    public function testGetOrCreateUsingValidNameWithUnexistingDirectory()
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
        $this->assertInstanceOf('App\Model\FileCollection', $fileCollection);
        $this->assertEquals(32, strlen($fileCollection->getName()));
        $this->assertDirectoryExists($fileCollection->getDirectory());
        $this->assertDirectoryIsReadable($fileCollection->getDirectory());

        @rmdir($fileCollection->getDirectory());
    }
}
