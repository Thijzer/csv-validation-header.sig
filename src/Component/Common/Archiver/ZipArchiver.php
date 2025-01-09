<?php

namespace Misery\Component\Common\Archiver;

use Misery\Component\Common\FileManager\Exeption\FileNotFoundException;
use Misery\Component\Common\FileManager\LocalFileManager;

class ZipArchiver implements ArchiverInterface
{
    /** @var LocalFileManager */
    private $fileManager;

    public function __construct(LocalFileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    /** @inheritDoc */
    public function compress(string $filePath): void
    {
    }

    /** @inheritDoc
     * @throws FileNotFoundException
     */
    public function decompress(string $filePath): void
    {
        $zip = new \ZipArchive();
        $zip->open($filePath);
        $zip->extractTo($this->fileManager->getWorkingDirectory());
    }
}