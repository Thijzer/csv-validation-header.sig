<?php

namespace Misery\Component\AttributeFormatter;

class PriceCollectionFormatter implements PropertyFormatterInterface, RequiresContextInterface
{
    private const FORMAT = '%amount% %currency%';
    private $thousandsSep;
    private $decimalPoint;
    private $decimal;

    public function __construct(int $decimal = 2, string $decimalPoint = '.', string $thousandsSep = ',')
    {
        $this->decimal = $decimal;
        $this->decimalPoint = $decimalPoint;
        $this->thousandsSep = $thousandsSep;
    }

    /**
     * The Value that needs formatting supplied with context if needed
     *
     * @param $values
     * @param array $context
     * @return string|array
     */
    public function format($values, array $context = [])
    {
        $format = $context['format'] ?? self::FORMAT;
        $decimal = $this->decimal;
        if (isset($context['decimal-attributes-to-format'], $context['decimals-to-use']) && in_array($context['current-attribute-code'], explode(',', $context['decimal-attributes-to-format']), true)) {
            $decimal = (int)$context['decimals-to-use'];
        }

        foreach ($values as $value) {
            if ($value['currency'] === $context['currency']) {

                $value['amount'] = str_replace(
                    $this->decimalPoint . sprintf('%-0' . $decimal . 's', ''),
                    '',
                    number_format(
                        $value['amount'],
                        $context['dec'] ?? $decimal,
                        $context['dec_point'] ?? $this->decimalPoint, $this->thousandsSep)
                );

                $value['format'] = $format;

                return $value;
            }
        }
    }

    /**
     * Check what values are supported
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return $type === 'pim_catalog_price_collection';
    }

    public function requires(array $context): bool
    {
        return isset($context['currency']);
    }
}