<?php

namespace Misery\Component\AttributeFormatter;

class BooleanLabelsAttributeFormatter implements PropertyFormatterInterface, RequiresContextInterface
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
        return $value ? $context['pdf-values']['label'] : '';
    }

    public function supports(string $type): bool
    {
        return $type === 'pim_catalog_boolean';
    }

    public function requires(array $context): bool
    {
        return isset($context['locale'], $context['boolean-label-format'], $context['pdf-values']['label']) && $context['boolean-label-format'] === true;
    }
}