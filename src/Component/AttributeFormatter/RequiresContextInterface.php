<?php

namespace Misery\Component\AttributeFormatter;

interface RequiresContextInterface
{
    /**
     * Some Formatters requires contextual elements
     * to work properly
     *
     * @param array $context
     * @return bool
     */
    public function requires(array $context): bool;
}