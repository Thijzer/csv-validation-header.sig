<?php

namespace Misery\Component\Akeneo;

use Misery\Component\Common\Picker\ValuePickerInterface;

/**
 *  expected/supported array values structures
 *
 * / local and scope
 *
 *     'description' => [
 *          'pim' => [
 *              'nl_BE' => 'LVS',
 *          ],
 *      ],
 *
 * / local
 *
 *     'description' => [
 *          'nl_BE' => 'LVS',
 *      ],
 *
 * / scope
 *
 *     'description' => [
 *          'pim' => 'LVS',
 *      ],
 *
 * / global
 *
 *     'description' => 'LVS',
 **/
class AkeneoValuePicker implements ValuePickerInterface
{
    private static $default = [
        'locale' => null,
        'scope' => null,
    ];

    public static function autoPick(array $sourceItem, $field, array $context): array|string
    {
        if (isset($context['locales'][0]) && count($context['locales']) === 1) {
            return self::pick($sourceItem, $field, ['locale' => $context['locales'][0]]);
        }
        if (isset($context['locales'][0]) && count($context['locales']) > 1) {
            foreach ($context['locales'] as $locale) {
                $tmp[$locale] = $sourceItem ? self::pick($sourceItem, $field, ['locale' => $locale]) : $sourceItem;
            }
            return $tmp;
        }
        if (isset($context['locale'])) {
            return self::pick($sourceItem, $field, $context);
        }

        throw new \Exception('Unsupported method class');
    }

    public static function pick(array $item, string $field, array $context = [])
    {
        $context = array_merge(self::$default, $context);

        $itemValue = $item[$field] ?? null;

        if ($itemValue) {
            if (null === $context['scope'] && null === $context['locale']) {
                return $itemValue;
            }
            if ($context['scope'] && $context['locale'] && isset($itemValue[$context['scope']][$context['locale']])) {
                return $itemValue[$context['scope']][$context['locale']];
            }
            elseif ($context['scope'] && isset($itemValue[$context['scope']])) {
                return $itemValue[$context['scope']];
            }
            elseif ($context['locale'] && isset($itemValue[$context['locale']])) {
                return $itemValue[$context['locale']];
            }
        }

        return null;
    }
}