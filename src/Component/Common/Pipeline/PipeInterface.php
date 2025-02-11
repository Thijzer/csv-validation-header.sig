<?php

namespace Misery\Component\Common\Pipeline;

interface PipeInterface
{
    public function pipe(array $item): array;
}