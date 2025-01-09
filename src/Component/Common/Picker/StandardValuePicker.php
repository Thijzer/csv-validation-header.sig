<?php

namespace Misery\Component\Common\Picker;

class StandardValuePicker implements ValuePickerInterface
{
    private static $default = [
        'locale' => null,
        'scope' => null,
    ];

    public static function pick(array $item, string $field, array $context = [])
    {
        $context = array_merge(self::$default, $context);

        $result = [];
        foreach ($item['values'][$field] ?? [] as $index => $itemValue) {
            switch (true) {
                case (null === $context['scope'] && null === $context['locale'] && $itemValue['scope'] === null && $itemValue['locale'] === null):
                case ($itemValue['locale'] === $context['locale'] && $itemValue['scope'] === $context['scope']):
                case ($itemValue['locale'] === $context['locale'] && $context['scope'] === null):
                case ($itemValue['scope'] === $context['scope'] && $context['locale'] === null):
                    $result[$index] = $itemValue;
                    break;
                default:
                    break;
            }
        }

        return $result;
    }
}