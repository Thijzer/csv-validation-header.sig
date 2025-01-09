<?php

namespace Misery\Component\Common\FileManager;

class LocalFileManager implements FileManagerInterface
{
    private $workingDirectory;
    private $removeEmptyDir;

    public function __construct(string $workingDirectory, bool $removeEmptyDir = true)
    {
        $absolute = realpath($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . $workingDirectory);
        $this->workingDirectory = is_dir($absolute) ? $absolute : $workingDirectory;
        $this->removeEmptyDir = $removeEmptyDir;
    }

    public function createSub(string $workingDirectory): self
    {
        $directory = $this->workingDirectory . DIRECTORY_SEPARATOR . $workingDirectory;
        $this->makePath($directory);

        return new self($directory);
    }

    /**
     * Only for local file management
     * Cannot have support in the interface
     */
    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    public function getFile(string $filename): string
    {
        return $this->getAbsolutePath($filename);
    }

    public function provisionPath(string $filename): string
    {
        return $this->getAbsolutePath($filename);
    }

    public function copyFile(string $filename, string $newFilename)
    {
        copy($this->getAbsolutePath($filename), $this->getAbsolutePath($newFilename));
    }

    public function moveFile(string $filename, string $newFilename)
    {
        rename($this->getAbsolutePath($filename), $this->getAbsolutePath($newFilename));
    }

    public function moveFiles($newFilename, array $filenames): void
    {
        foreach ($filenames as $filename) {
            $this->moveFile($filename, $newFilename);
        }
    }


    public function find(string $regex): \Iterator
    {
        return new \GlobIterator($this->getAbsolutePath($regex));
    }

    public function addFile(string $filename, $content)
    {
        file_put_contents($this->getAbsolutePath($filename), $content);
    }

    public function isFile(string $filename): bool
    {
        return file_exists($this->getAbsolutePath($filename));
    }

    public function getFileContent(string $filename)
    {
        return file_get_contents($this->getAbsolutePath($filename));
    }

    public function removeFiles(...$filenames): void
    {
        foreach ($filenames as $filename) {
            $this->removeFile($filename);
        }
    }

    public function removeFile(string $filename): void
    {
        $filename = $this->getAbsolutePath($filename);
        if (is_file($filename)) {
            unlink($filename);
        }

        if ($this->removeEmptyDir) {
            $this->removeEmptyDirectory($this->getDirectory($filename));
        }
    }

    public function listFilesRecursive(string $subDirectory = null): \Generator
    {
        $scandir = $subDirectory ?? $this->workingDirectory;
        foreach (glob($scandir."/*") as $path) {
            if (is_dir($path)) {
                foreach ($this->listFilesRecursive($path) as $subpath) {
                    yield $subpath;
                }
                continue;
            }
            if (is_file($path)) {
                yield $path;
            }
        }
    }

    public function listFiles(): \Generator
    {
        foreach (glob($this->workingDirectory."/*") as $path) {
            if (is_file($path)) {
                yield $path;
            }
        }
    }

    /**
     * Making Path's is irrelevant in object storage
     * We make path's if needed
     * @param string $directory
     */
    private function makePath(string $directory)
    {
        if (!file_exists($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }
    }

    /**
     * @param string $filename
     * @return mixed|string
     */
    private function getDirectory(string $filename)
    {
        return pathinfo($filename)['dirname'];
    }

    /**
     * Returns Absolute paths even when entering Relative ones.
     * Only Local FS required absoluteness
     *
     * @param string $filename
     * @return string
     */
    public function getAbsolutePath(string $filename): string
    {
        $this->makePath($this->getDirectory($filename));

        if (strpos($filename, $this->workingDirectory) === false) {
            return $this->workingDirectory. DIRECTORY_SEPARATOR . $filename;
        }

        return $filename;
    }

    /**
     * Removes the empty Directory and subdirectory
     * Imitation object storage behavior as we don't care about directory or directory structures.
     *
     * @param string $directory
     *
     * @return bool
     */
    private function removeEmptyDirectory(string $directory): bool
    {
        $empty = true;
        foreach (glob($directory.DIRECTORY_SEPARATOR."*") as $file) {
            $empty &= is_dir($file) && $file !== $this->workingDirectory && $this->removeEmptyDirectory($file);
        }

        return $empty && rmdir($directory);
    }

    public function clear(): void
    {
        $this->removeFiles(...$this->listFiles());
    }
}