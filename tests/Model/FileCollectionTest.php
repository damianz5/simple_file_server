<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\FileCollection;
use PHPUnit\Framework\TestCase;

class FileCollectionTest extends TestCase
{
    public function testFileCollectionModel(): void
    {
        $fileCollectionName = 'testname';
        $fileCollectionDirectory = 'some/directory/';

        $fileCollection = new FileCollection($fileCollectionName, $fileCollectionDirectory);

        $this->assertInstanceOf(FileCollection::class, $fileCollection);
        $this->assertEquals($fileCollection->getName(), $fileCollectionName);
        $this->assertEquals($fileCollection->getDirectory(), $fileCollectionDirectory);
    }
}
