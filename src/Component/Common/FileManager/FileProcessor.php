<?php

namespace Misery\Component\Common\FileManager;

class FileProcessor implements FileProcessorInterface
{
    /** @var LocalFileManager */
    private $fm;
    private $dateFormat;
    private $processId;

    public function __construct(LocalFileManager $fileManager, string $processId = null, $dateFormat = 'Y-m-d')
    {
        $this->fm = $fileManager;
        $this->processId = $processId ?? uniqid();
        $this->dateFormat = $dateFormat;
    }

    public function getIncomingDirectory(string $filename = null): string
    {
        return $this->provisionRelativePath('incoming', $filename);
    }

    public function getProcessingDirectory(string $filename = null): string
    {
        return $this->provisionRelativePath('processing', $filename);
    }

    public function getDoneDirectory(string $filename = null): string
    {
        return $this->provisionRelativePath('done', date($this->dateFormat), $filename);
    }

    public function getFailedDirectory(string $filename = null): string
    {
        return $this->provisionRelativePath('failed', date($this->dateFormat), $filename);
    }

    /**
     * return relative paths
     * pass these into the filemanager
     */
    private function provisionRelativePath(...$chunks): string
    {
        return implode(DIRECTORY_SEPARATOR, array_filter($chunks));
    }

    public function clearProcessingDirectory(): void
    {
        $this->fm->removeFiles(
            iterator_to_array(
                $this->fm->find($this->getProcessingDirectory('*'))
            )
        );
    }

    public function getFileContent(string $filename)
    {
        return file_get_contents($this->getProcessingDirectory($filename));
    }

    public function findFiles(string $regex): \Generator
    {
        foreach (new \GlobIterator($this->getProcessingDirectory($regex)) as $file) {
            if (is_file($file)) {
                yield $file;
            }
        }
    }

    public function listFiles(): \Generator
    {
        foreach (glob($this->getProcessingDirectory('*')) as $file) {
            if (is_file($file)) {
                yield $file;
            }
        }
    }

    public function createProcessableFiles(): void
    {
        $this->fm->moveFiles(
            $this->getProcessingDirectory(),
            iterator_to_array(
                $this->fm->find($this->getIncomingDirectory('*'))
            )
        );
    }

    private function processFileToFailed(string $filename): string
    {
        $this->fm->moveFile(
            $this->getProcessingDirectory($filename),
            $filename = $this->getFailedDirectory($filename)
        );

        return $filename;
    }

    private function processFileToDone(string $filename): string
    {
        $this->fm->moveFile(
            $this->getProcessingDirectory($filename),
            $filename = $this->getDoneDirectory($filename)
        );

        return $filename;
    }
}
