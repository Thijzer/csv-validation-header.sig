<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Decoder\ItemDecoder;

class DecodingPipe implements PipeInterface
{
    private $decoder;

    public function __construct(ItemDecoder $decoder)
    {
        $this->decoder = $decoder;
    }
    public function pipe(array $item): array
    {
        return $this->decoder->decode($item);
    }
}