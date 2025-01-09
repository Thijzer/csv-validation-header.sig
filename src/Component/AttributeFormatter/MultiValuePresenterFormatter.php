<?php

namespace Misery\Component\AttributeFormatter;

use Misery\Component\Common\Utils\ValueFormatter;

class MultiValuePresenterFormatter implements PropertyFormatterInterface, RequiresContextInterface
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
        $format = $value['format'] ?? $context['format'] ?? null;
        $valueSeparator = $context['value-separator'] ?? ', '; # best fallback option
        if ($context['current-attribute-type'] === 'pim_catalog_multiselect' && empty($value)) {
            return '';
        }

        if (empty($value)) {
            return $value;
        }

        if (is_array($value) && $format) {
            return ValueFormatter::format($format, $value);
        }

        if (is_array($value) && !$format && isset($valueSeparator)) {
            $value = implode($valueSeparator, $value);
        }

        if (is_string($value) && $format) {
            return ValueFormatter::format($format, ['value' => $value]);
        }

        return $value;
    }

    /**
     * Check what values are supported
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        // we support all types
        return true;
    }

    public function requires(array $context): bool
    {
        return true;
    }
}