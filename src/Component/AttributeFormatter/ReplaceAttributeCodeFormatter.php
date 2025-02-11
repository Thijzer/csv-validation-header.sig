<?php

namespace Misery\Component\AttributeFormatter;

use Misery\Component\Source\Source;

/**
 * This formatter allows attribute code replacement
 */
class ReplaceAttributeCodeFormatter implements PropertyFormatterInterface, RequiresContextInterface
{
    private Source $source;
    private array $supportedTypes = [
        'pim_reference_data' =>  [
            'pim_reference_data_simpleselect',
            'pim_reference_data_multiselect',
        ],
        'pim_catalog' => [
            'pim_catalog_simpleselect',
            'pim_catalog_multiselect',
        ],
    ];

    public function __construct(Source $source, string $supportedType = 'pim_catalog')
    {
        $this->source = $source;
        $this->supportedTypes = $this->supportedTypes[$supportedType];
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
        if (is_array($value)) {
            $tmp = [];
            foreach ($value as $valueItem) {
                $tmp[] = $this->format($valueItem, $context);
            }
            return $tmp;
        }

        $separator = $context['separator'] ?? '-';

        $this->recursiveReplace($context, '{value}', $value);
        $this->recursiveReplace($context, '{attribute-code}', $context['current-attribute-code']);

        if (isset($context['filter'])) {
            $sourceItem = $this->getItem($context['filter']);
            if (null === $sourceItem) {
                return $value;
            }

            if (is_string($context['return'])) {
                return $this->getValueByFormat($sourceItem, $context['return'], $separator);
            }

            if (is_array($context['return'])) {
                $tmp = [];
                foreach ($context['return'] as $returnField) {
                    $tmp[] = $this->getValueByFormat($sourceItem, $returnField, $separator);
                }
                return $tmp;
            }
        }

        return $value;
    }

    private function getItem(array $filter)
    {
        return $this->source->getReader()
            ->find($filter)
            ->getIterator()
            ->current()
        ;
    }

    /**
     * Check what values are supported
     * @param string $type
     * @return bool
     */
    public function supports(string $type): bool
    {
        return in_array($type, $this->supportedTypes);
    }

    public function requires(array $context): bool
    {
        return isset($context['return']) && isset($context['filter']);
    }

    function getValueByFormat(array $data, string $format, string $separator = '-'): ?string
    {
        if (isset($data[$format])) {
            return $data[$format];
        }

        $keys = explode($separator, $format);
        $value = $data;
        foreach ($keys as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            }
        }

        return $value;
    }

    private function recursiveReplace(&$array, $search, $replace): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->recursiveReplace($value, $search, $replace);
                continue;
            }
            if (is_string($value) && is_string($replace)) {
                $value = str_replace($search, $replace, $value);
            }
        }
    }
}