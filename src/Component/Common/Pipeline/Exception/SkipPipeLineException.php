<?php

namespace Misery\Component\Common\Pipeline\Exception;

class SkipPipeLineException extends \Exception
{
    public function __construct(string $message, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}