<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Item\TypeGuesser;

class LoggingPipe implements PipeInterface
{

    public function pipe(array $item): array
    {
        dump('Result', $item);
        return $item;
    }
}
