<?php

namespace Misery\Component\Common\FileManager\Exeption;

class FileNotFoundException extends \Exception
{
    public function __construct(string $path = null, int $code = 0, \Throwable $previous = null)
    {
        if (null === $path) {
            $message = 'File could not be found.';
        } else {
            $message = sprintf('File "%s" could not be found.', $path);
        }

        parent::__construct($message, $code, $previous);
    }
}