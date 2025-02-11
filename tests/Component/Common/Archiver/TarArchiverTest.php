<?php

namespace Tests\Misery\Component\Common\Archiver;

use Misery\Component\Common\Archiver\CompressedTarArchiver;
use Misery\Component\Common\FileManager\LocalFileManager;
use PHPUnit\Framework\TestCase;

class TarArchiverTest extends TestCase
{
    public function test_it_should_compress(): void
    {
        $manager = new LocalFileManager(__DIR__.DIRECTORY_SEPARATOR.'data');
        $manager = $manager->createSub(uniqid());

        $archiver = new CompressedTarArchiver($manager);

        $manager->addFile('test.nothing', 'NO_CONTENT');

        $archiver->compress('test.tar.gz');

        self::assertCount(1, iterator_to_array($manager->listFiles()));

        self::assertFileDoesNotExist($manager->getAbsolutePath('test.nothing'));
        self::assertFileExists($manager->getAbsolutePath('test.tar.gz'));

        $manager->removeFile('test.tar.gz');
    }

    public function test_it_should_decompress(): void
    {
        $manager = new LocalFileManager(__DIR__.DIRECTORY_SEPARATOR.'data');
        $manager = $manager->createSub(uniqid());

        $archiver = new CompressedTarArchiver($manager);

        $manager->addFile('test.nothing', 'NO_CONTENT');

        $archiver->compress('test.tar.gz');

        $archiver->decompress('test.tar.gz');

        self::assertFileExists($manager->getAbsolutePath('test.nothing'));

        $manager->removeFile('test.nothing');
    }
}