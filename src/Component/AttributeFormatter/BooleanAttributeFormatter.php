<?php

namespace Misery\Component\AttributeFormatter;

class BooleanAttributeFormatter implements PropertyFormatterInterface, RequiresContextInterface
{
    /**
     * The Value that needs formatting supplied with context if needed
     *
     * @param $value
     * @param array $context
     * @return string|array
     */
    public function format($value, array $context = [])
    {
        if (true === $value || $value === '1' || $value === 1) {
            return $context['label']['Y'];
        }

        if (false === $value || $value === '0' || $value === 0) {
            return $context['label']['N'];
        }

        return $value;
    }

    public function supports(string $type): bool
    {
        return $type === 'pim_catalog_boolean';
    }

    public function requires(array $context): bool
    {
        $type = $context['current-attribute-type'];

        return
            (isset($context['label']['Y']) &&
            isset($context['label']['N']))
            ||
            (isset($context[$type]['label']['Y']) &&
                isset($context[$type]['label']['N']))
            ;
    }
}