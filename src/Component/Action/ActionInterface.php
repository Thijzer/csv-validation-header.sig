<?php

namespace Misery\Component\Action;

interface ActionInterface
{
    public function apply(array $item): array;
}