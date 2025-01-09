<?php

namespace Misery\Component\Extension;

interface ExtensionInterface
{
    public function apply(array $item): array;
}