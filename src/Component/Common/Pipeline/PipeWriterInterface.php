<?php

namespace Misery\Component\Common\Pipeline;

interface PipeWriterInterface
{
    public function write(array $data): void;
    public function stop(): void;
}