<?php

namespace Misery\Component\Common\FileManager;

use Assert\Assert;

class InMemoryFileManager implements FileManagerInterface
{
    private array $files = [];
    private array $aliases = [];

    public static function createFromFileManager(FileManagerInterface $fileManager): self
    {
        $fm = new self();
        $fm->addFiles($fileManager->listFiles());

        return $fm;
    }

    public function addAliases(array $aliases): void
    {
        $this->aliases = array_merge($this->aliases, $aliases);

        foreach ($aliases as $aliasName => $pattern) {
            $matches = array_filter($this->getAliases(), function($key) use ($pattern) {
                return fnmatch(pathinfo($pattern, PATHINFO_BASENAME), $key);
            });
            if (count($matches) === 1) {
                $this->files[$aliasName] = $this->files[current($matches)];
            }
        }
    }

    public function getAliases(): array
    {
        return array_keys($this->files);
    }

    public function addFromFileManager(FileManagerInterface $sourceCollection): void
    {
        $this->addFiles($sourceCollection->listFiles());
    }

    public function getFile(string $filename): string
    {
        $file = $this->files[$filename] ?? null;
        if ($file) {
            return  $file;
        }
        throw new \Exception(sprintf('File %s not found', $filename));
    }

    public function addFiles($files): void
    {
        Assert::that($files)->isTraversable();

        foreach ($files as $file) {
            Assert::that($file)->file();
            $this->files[pathInfo($file, PATHINFO_BASENAME)] = $file;
        }
    }

    public function addFile(string $filename, $content)
    {
        // TODO: Implement addFile() method.
    }

    public function getFileContent(string $filename)
    {
        // TODO: Implement getFileContent() method.
    }

    public function removeFile(string $filename): void
    {
        // TODO: Implement removeFile() method.
    }

    public function removeFiles(...$filenames): void
    {
        // TODO: Implement removeFiles() method.
    }

    public function isFile(string $filename): bool
    {
        // TODO: Implement isFile() method.
    }

    public function listFiles(): \Generator
    {
        foreach ($this->files as $alias => $file) {
            if (is_file($file)) {
                yield $alias => $file;
            }
        }
    }

    public function clear(): void
    {
        // TODO: Implement clear() method.
    }

    public function provisionPath(string $filename): string
    {
    }
}