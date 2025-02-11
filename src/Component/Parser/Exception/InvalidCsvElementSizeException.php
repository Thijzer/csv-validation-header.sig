<?php

namespace Misery\Component\Parser\Exception;

use Throwable;

class InvalidCsvElementSizeException extends \Exception
{
    public function __construct(string $filename, int $line, array $headers, array $item, Throwable $previous = null)
    {
        parent::__construct(sprintf(
            'Invalid CSV Element size on file(%s) : lineNumber(%s) : headers(%s): item(%s)',
            $filename,
            $line,
            json_encode($headers),
            json_encode($item)
        ), 0, $previous);
    }
}