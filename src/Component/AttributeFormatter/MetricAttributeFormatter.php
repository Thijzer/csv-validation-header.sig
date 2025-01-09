<?php

namespace Misery\Component\AttributeFormatter;

use Misery\Component\Common\Utils\ValueFormatter;

class MetricAttributeFormatter implements PropertyFormatterInterface
{
    private const FORMAT = '%amount% %unit%';

    private $thousandsSep;
    private $decimalPoint;
    private $decimal;

    public function __construct(int $decimal = 0, string $decimalPoint = '.', string $thousandsSep = ',')
    {
        $this->decimal = $decimal;
        $this->decimalPoint = $decimalPoint;
        $this->thousandsSep = $thousandsSep;
    }

    /**
     * The Value that needs formatting supplied with context if needed
     *
     * @param $value
     * @param array $context
     * @return string|array
     */
    public function format($value, array $context = [])
    {
        if (isset($context['metric-display-unit']) && !isset($context['format']) && $context['metric-display-unit'] === false) {
            $context['format'] = '%amount%';
        }

        if ((!isset($context['metric-display-unit']) || $context['metric-display-unit']) && !isset($context['format']) && isset($context['metric-separator'])) {
            $context['format'] = '%amount%' . $context['metric-separator'] . '%unit%';
        }

        $format = $context['format'] ?? self::FORMAT;

        if (false == isset($value['amount'])) {
            return null;
        }

        // TODO needs it's own formatter
        if (isset($context['map'])) {
            $value['unit'] = $context['map'][$value['unit']] ?? $value['unit'];
        }

        $value['format'] = $format;

        return $value;
    }

    /**
     * Check what values are supported
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $type === 'pim_catalog_metric';
    }
}