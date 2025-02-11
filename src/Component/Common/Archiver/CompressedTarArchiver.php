<?php

namespace Misery\Component\Common\Archiver;

use Misery\Component\Common\FileManager\Exeption\FileNotFoundException;
use Misery\Component\Common\FileManager\LocalFileManager;

class CompressedTarArchiver implements ArchiverInterface
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
        $files = iterator_to_array($this->fileManager->listFiles());

        $pseudoFile = $this->fileManager->getAbsolutePath(uniqid(). '.tar');

        $p = new \PharData($pseudoFile);

        $p->buildFromDirectory($this->fileManager->getWorkingDirectory());

        $p->compress(\Phar::GZ);

        foreach ($files as $file) {
            $this->fileManager->removeFile($file);
        }
        $this->fileManager->removeFile($pseudoFile);

        $this->fileManager->moveFile($pseudoFile.'.gz', $filePath);
    }

    /** @inheritDoc
     * @throws FileNotFoundException
     */
    public function decompress(string $filePath): void
    {
        if (false === $this->fileManager->isFile($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $filePath = $this->fileManager->getAbsolutePath($filePath);

        // '/path/to/my.tar.gz'
        $p = new \PharData($filePath);

        // creates /path/to/my.tar
        $p->decompress();

        // unarchived from the tar
        $tarPath = $this->getTarPath($filePath);
        $phar = new \PharData($tarPath);

        $phar->extractTo($this->fileManager->getWorkingDirectory());

        // success then remove residu
        $this->fileManager->removeFile($filePath);
        $this->fileManager->removeFile($tarPath);
    }

    private function getTarPath(string $filePath): string
    {
        return substr($filePath, 0 , (strrpos($filePath, '.')));
    }
}