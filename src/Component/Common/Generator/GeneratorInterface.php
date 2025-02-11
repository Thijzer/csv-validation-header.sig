<?php

namespace Misery\Component\Common\Generator;

interface GeneratorInterface
{
    public function generate(string $endPoint, ...$data): string;
}