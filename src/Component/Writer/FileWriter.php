<?php

namespace Misery\Component\Writer;

abstract class FileWriter implements ItemWriterInterface
{
    protected $handle;
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->handle = fopen($filename, 'wb+');
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function clear(): void
    {
        file_put_contents($this->filename, '');
    }

    public function close(): void
    {
        if(is_resource($this->handle)) {
            @fclose($this->handle);
        }
    }

    public function __destruct()
    {
        $this->close();
    }
}