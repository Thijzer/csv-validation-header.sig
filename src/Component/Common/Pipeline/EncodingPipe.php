<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Encoder\ItemEncoder;

class EncodingPipe implements PipeInterface
{
    /** @var ItemEncoder */
    private $encoder;

    public function __construct(ItemEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function pipe(array $item): array
    {
        return $this->encoder->encode($item);
    }
}