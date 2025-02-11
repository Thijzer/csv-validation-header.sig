<?php

namespace Misery\Component\Common\Archiver;

interface ArchiverInterface
{
    /** @param string $filePath /path/to/compressed.tar.gz file you wish to compress into */
    public function compress(string $filePath): void;

    /** @param string $filePath /path/to/compressed.tar.gz file you wish to decompress */
    public function decompress(string $filePath): void;
}