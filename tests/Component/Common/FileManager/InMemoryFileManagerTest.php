<?php

namespace Tests\Component\Common\FileManager;

use Misery\Component\Common\FileManager\InMemoryFileManager;
use PHPUnit\Framework\TestCase;

class InMemoryFileManagerTest extends TestCase
{
    public function testAddFiles()
    {
        $fileManager = new InMemoryFileManager();
        $files = [
            'file1.txt' => __DIR__ . '/../../../examples/dummies/file1.txt',
            'file2.txt' => __DIR__ . '/../../../examples/dummies/file2.txt',
        ];

        $fileManager->addFiles($files);

        $this->assertCount(2, $fileManager->getAliases());
        $this->assertEquals(__DIR__ .'/../../../examples/dummies/file1.txt', $fileManager->getFile('file1.txt'));
        $this->assertEquals(__DIR__ .'/../../../examples/dummies/file2.txt', $fileManager->getFile('file2.txt'));
    }

    public function testAddAliases()
    {
        $fileManager = new InMemoryFileManager();
        $files = [
            'file1.txt' => __DIR__ . '/../../../examples/dummies/file1.txt',
            'file2.txt' => __DIR__ . '/../../../examples/dummies/file2.txt',
        ];
        $aliases = [
            'alias1' => 'file1.*',
            'alias2' => 'file1.txt',
        ];

        $fileManager->addFiles($files);
        $fileManager->addAliases($aliases);

        $this->assertCount(4, $fileManager->getAliases());
        $this->assertEquals(__DIR__ .'/../../../examples/dummies/file1.txt', $fileManager->getFile('alias1'));
        $this->assertEquals(__DIR__ .'/../../../examples/dummies/file1.txt', $fileManager->getFile('alias2'));
        $this->assertEquals(__DIR__ .'/../../../examples/dummies/file1.txt', $fileManager->getFile('file1.txt'));
    }

    public function testGetFileNotFound()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("File non_existent.txt not found");

        $fileManager = new InMemoryFileManager();
        $files = [
            'file1.txt' => __DIR__ . '/../../../examples/dummies/file1.txt',
            'file2.txt' => __DIR__ . '/../../../examples/dummies/file2.txt',
        ];

        $fileManager->addFiles($files);

        $fileManager->getFile('non_existent.txt');
    }
}
