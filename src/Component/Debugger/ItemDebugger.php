<?php

namespace Misery\Component\Debugger;

use Misery\Component\Item\TypeGuesser;

class ItemDebugger
{
    public function log($item, $message)
    {
        dump($message, $item);
    }
}