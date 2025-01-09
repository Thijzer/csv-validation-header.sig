<?php

namespace Misery\Component\AttributeFormatter;

class NumberAttributeFormatter implements PropertyFormatterInterface
{
    private string $thousandsSep = ',';
    private string $decimalPoint = '.';
    private int $decimal = 0;

    public function format($value, array $context = [])
    {
        $decimal = (int) ($context['decimals'] ?? $this->decimal);
        $thousandsSep = (string) ($context['thousands-sep'] ?? $this->thousandsSep);
        $decimalPoint = (string) ($context['decimal-point'] ?? $this->decimalPoint);

        if (is_array($value) && array_key_exists('amount', $value)) {
            if ($value['amount'] === null) {
                return null;
            }

            $value['amount'] = number_format($value['amount'], $decimal, $decimalPoint, $thousandsSep);

            return $value;
        }

        return number_format($value, $decimal, $decimalPoint, $thousandsSep);
    }

    public function supports(string $type): bool
    {
        return in_array($type, ['pim_catalog_number', 'pim_catalog_metric']);
    }
}