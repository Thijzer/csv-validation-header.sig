<?php

namespace Misery\Component\Common\Pipeline;

interface PipeReaderInterface
{
    public function read();
    public function stop(): void;
}