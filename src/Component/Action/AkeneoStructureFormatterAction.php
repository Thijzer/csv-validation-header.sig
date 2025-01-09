<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Converter\Matcher;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Statement\ItemPlaceholder;

class AkeneoStructureFormatterAction implements OptionsInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    public const NAME = 'akeneo_structure_format';

    /** @var array */
    private $options = [
        'fields' => [],
        'list' => null,
        'matcher_structure' => true,
        'context' => [
            'active_scopes' => [],
            'active_locales' => [],
            'active_locales_per_channel' => [],
            'locale_mapping' => [],
            'scope_mapping' => [],
        ],
        'format_key' => null,
        'attributes_source' => null,
    ];

    public function apply(array $item): array
    {
        $context = $this->getOption('context');
        $activeLocales = $context['active_locales'] ?? [];
        $activeLocalesPerChannel = $context['active_locales_per_channel'] ?? [];
        $activeScopes = $context['active_scopes'] ?? [];
        $restructures = $this->getOption('restructure');
        $attributesSource = $this->getOption('attributes_source');
        if (null === $attributesSource) {
            return $item;
        }
        /** @var ItemReader $attributes */
        $attributes = $this->getConfiguration()->getSources()->get($attributesSource)->getCachedReader();

        foreach ($restructures as $restructure) {
            $output = [];
            $fieldName = $restructure['field'];
            $structure = $restructure['structure'];
            $struct = $restructure['struct'];
            $fieldValue = $item[$fieldName];
            $attribute = $attributes->find(['code' => $fieldName])->getIterator()->current();
            $localizable = $attribute['localizable'];
            $scopable = $attribute['scopable'];
            // active check on the localized array
            if ($struct === 'localized_items_array' && is_array($fieldValue) && is_array($fieldValue[0])) {

                if (!$localizable) {
                    continue;
                }
                // localizable loop
                foreach ($fieldValue as $fieldValueData) {
                    // let's assemble the data correctly
                    $value = [
                        'locale' => isset($structure['locale']) ? ItemPlaceholder::replace($structure['locale'], $fieldValueData+$item): null,
                        'scope' => isset($structure['scope']) ? ItemPlaceholder::replace($structure['scope'], $fieldValueData+$item): null,
                        'data' => isset($structure['data']) ? ItemPlaceholder::replace($structure['data'], $fieldValueData+$item): null,
                    ];

                    // TODO locale_mapping
                    if ($value['locale'] && $activeLocales !== [] && !in_array($value['locale'], $activeLocales)) {
                        continue;
                    }

                    if ($scopable && $value['scope'] && !in_array($value['scope'], $activeScopes)) {
                        continue;
                    }

                    // TODO scope_mapping

                    // scopable loop
                    if ($scopable && null === $value['scope']) {
                        //dump($value['locale']);
                        foreach ($activeScopes as $activeScope) {
                            $activeScopeLocales = $activeLocalesPerChannel[$activeScope] ?? [];
                            //dump($activeLocales);
                            if ($activeScopeLocales !== [] && in_array($value['locale'], $activeScopeLocales)) {
                                $matcher = Matcher::create('values|'.$fieldName, $value['locale'], $activeScope);
                                $item[$matcher->getRowKey()] = $value['data'];
                                unset($item[$fieldName]);
                                if ($this->getOption('matcher_structure')) {
                                    $value['scope'] = $activeScope;
                                    $output[$k = $matcher->getMainKey()] = $value;
                                    $output[$k]['matcher'] = $matcher;
                                }
                            }
                        }
                    }
                }
            }
            if ($output !== []) {
                $item[$fieldName] = $output;
            }
        }

        return $item;
    }
}