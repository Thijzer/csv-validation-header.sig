<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Converter\ConverterInterface;

class RevertPipe implements PipeInterface
{
    /** @var ConverterInterface */
    private $converter;

    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    public function pipe(array $item): array
    {
        return $this->converter->revert($item);
    }
}