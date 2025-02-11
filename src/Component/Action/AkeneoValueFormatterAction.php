<?php

namespace Misery\Component\Action;

use Misery\Component\AttributeFormatter\AttributeValueFormatter;
use Misery\Component\AttributeFormatter\BooleanAttributeFormatter;
use Misery\Component\AttributeFormatter\BooleanLabelsAttributeFormatter;
use Misery\Component\AttributeFormatter\MetricAttributeFormatter;
use Misery\Component\AttributeFormatter\MultiValuePresenterFormatter;
use Misery\Component\AttributeFormatter\NumberAttributeFormatter;
use Misery\Component\AttributeFormatter\PriceCollectionFormatter;
use Misery\Component\AttributeFormatter\PropertyFormatterRegistry;
use Misery\Component\AttributeFormatter\ReplaceAttributeCodeFormatter;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Converter\Matcher;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ReaderAwareTrait;
use Misery\Component\Source\SourceTrait;

class AkeneoValueFormatterAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'akeneo_value_format';
    private ?AttributeValueFormatter $attributeValueFormatter = null;

    /** @var array */
    private $options = [
        'fields' => [],
        'list' => null,
        'context' => [],
        'format_key' => null,
        'filter_list' => [],
    ];

    public function apply(array $item): array
    {
        $context = $this->getOption('context');
        $fields = $this->getOption('list', $this->getOption('fields'));

        if ($this->attributeValueFormatter === null) {
            $registry = new PropertyFormatterRegistry();

            // Single source allowed ATM, else giant refactor
            if (
                isset($context['pim_catalog_simpleselect']['source']) ||
                isset($context['pim_catalog_multiselect']['source'])
            ) {
                $sourceAlias = $context['pim_catalog_simpleselect']['source'] ?? $context['pim_catalog_multiselect']['source'];
                $registry->addAll(
                    new ReplaceAttributeCodeFormatter($this->getConfiguration()->getSources()->get($sourceAlias), 'pim_catalog')
                );
            }
            // TBD reference data has only global labels
//            if (
//                isset($context['pim_reference_data_simpleselect']['source']) ||
//                isset($context['pim_reference_data_multiselect']['source'])
//            ) {
//                $sourceAlias = $context['pim_reference_data_simpleselect']['source'] ?? $context['pim_reference_data_multiselect']['source'];
//                $registry->addAll(
//                    new ReplaceAttributeCodeFormatter($this->getConfiguration()->getSources()->get($sourceAlias), 'pim_reference_data')
//                );
//            }

            $registry->addAll(
                new NumberAttributeFormatter(),
                new BooleanLabelsAttributeFormatter(),
                new BooleanAttributeFormatter(),
                new MetricAttributeFormatter(),
                new PriceCollectionFormatter(),
                new MultiValuePresenterFormatter(),
            );

            $this->attributeValueFormatter = new AttributeValueFormatter($registry);
            $this->attributeValueFormatter->setAttributeTypesAndCodes($this->getOption('filter_list'));
            $this->attributeValueFormatter->setFocusGroup(array_keys($context));
        }

        // key represents the references that could match item keys
        foreach ($fields as $field) {
            // converted data
            $key = $this->findMatchedValueData($item, $field);
            if ($key && $this->attributeValueFormatter->needsFormatting($field)) {
                $item[$key]['data'] = $this->attributeValueFormatter->format($field, $item[$key]['data'], $context);
                continue;
            }
            // flat data
            $itemValue = $item[$field] ?? null;
            if ($itemValue && $this->attributeValueFormatter->needsFormatting($field)) {
                $item[$field] = $this->attributeValueFormatter->format($field, $item[$field], $context);
            }
        }

        return $item;
    }

    private function findMatchedValueData(array $item, string $field): int|string|null
    {
        foreach ($item as $key => $itemValue) {
            $matcher = $itemValue['matcher'] ?? null;
            /** @var $matcher Matcher */
            if ($matcher && $matcher->matches($field)) {
                return $key;
            }
        }

        return null;
    }
}