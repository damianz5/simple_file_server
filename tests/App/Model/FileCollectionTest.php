<?php

namespace Tests\App\Model;

use App\Model\FileCollection;

class FileCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testFileCollectionModel()
    {
        $fileCollectionName = 'testname';
        $fileCollectionDirectory = 'some/directory/';

        $fileCollection = new FileCollection($fileCollectionName, $fileCollectionDirectory);

        $this->assertInstanceOf('App\Model\FileCollection', $fileCollection);
        $this->assertEquals($fileCollection->getName(), $fileCollectionName);
        $this->assertEquals($fileCollection->getDirectory(), $fileCollectionDirectory);
    }

}
