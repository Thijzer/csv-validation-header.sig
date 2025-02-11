<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Action\ItemActionProcessor;

class ActionPipe implements PipeInterface
{
    private $actionProcessor;

    public function __construct(ItemActionProcessor $actionProcessor)
    {

        $this->actionProcessor = $actionProcessor;
    }

    public function pipe(array $item): array
    {
        return $this->actionProcessor->process($item);
    }
}