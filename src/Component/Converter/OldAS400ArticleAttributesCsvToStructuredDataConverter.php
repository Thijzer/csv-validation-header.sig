<?php

namespace Misery\Component\Converter;

use Misery\Component\Akeneo\Header\AkeneoHeader;
use Misery\Component\Akeneo\Header\AkeneoHeaderTypes;
use Misery\Component\Akeneo\Header\AkeneoHeaderFactory;
use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationAwareInterface;
use Misery\Component\Configurator\ConfigurationTrait;
use Misery\Component\Reader\ItemReaderInterface;

class OldAS400ArticleAttributesCsvToStructuredDataConverter implements ConverterInterface, OptionsInterface, RegisteredByNameInterface, ConfigurationAwareInterface
{
    use OptionsTrait;
    use ConfigurationTrait;

    /** @var AkeneoHeader */
    private $header;
    private $options = [
        'attributes:list' => [],
        'localizable_codes:list' => [],
        'akeneo-mapping:list' => [],
        'locales' => [],
    ];

    /** @var ItemReaderInterface */
    private $cachedReader;

    public function convert(array $itemCollection): array
    {
        if (null === $this->header) {

            // akeneo mapping filter
            $this->setOption('akeneo-mapping:list', array_map(function($listItem) {
                $path = pathinfo($listItem);
                if ($path['extension'] == 'standard') {
                    return $listItem;
                }
                return $path['extension'];
            }, $this->getOption('akeneo-mapping:list')));

            // header Factory
            $this->header = (new AkeneoHeaderFactory())->create(
                $this->getOption('attributes:list'),
                $this->getOption('localizable_codes:list'),
                $this->getOption('locales')
            );

            $this->cachedReader = $this
                ->getConfiguration()
                ->getSources()
                ->get($this->getOption('attribute_options_file'))
                ->getCachedReader(['cache_size' => CachedCursor::EXTRA_LARGE_CACHE_SIZE])
            ;
        }

        $akeneoMapping = $this->getOption('akeneo-mapping:list');

        $output = [];
        $invalid_msgs = [];

        foreach ($itemCollection as $item) {

            $context = $this->header->getContext($item['UID']);
            $output['sku'] = $item['SKU'];

            if (in_array($context['type'], [AkeneoHeaderTypes::SELECT, AkeneoHeaderTypes::MULTISELECT])) {
                $item['VALUE_NL'] = $selectCode = $this->findSelectCode($item['UID'], $item['VALUE_NL']);
                if ($selectCode === null) {
                    throw new InvalidItemException(
                        sprintf(
                            'Unable to find Select Code from UID: %s and VALUE_NL %S',
                            $item['UID'],
                            $item['VALUE_NL']
                        ),
                        $output
                    );
                }
            }

            if ($context['type'] === AkeneoHeaderTypes::PRICE && $item['UOM']) {
                continue;
                //$item['UID'] = $this->header->createItemHeader($item['UID'], ['extra' => 'EUR']);
            }

            if (in_array($context['type'], [AkeneoHeaderTypes::METRIC, 'pim_catalog_metric_as400']) && $item['VALUE_NL']) {
                $output[$this->header->createItemHeader($item['UID'], ['extra' => 'unit'])] = $akeneoMapping[$item['UOM']];
            }

//
//            if ($context['has_locale'] === true) {
//                $output[$this->header->createItemHeader($item['ATTRIBUTE_CODE'], ['locale' => 'nl_BE'])] = $item['ATTRIBUTE_VALUE'];
//                $output[$this->header->createItemHeader($item['ATTRIBUTE_CODE'], ['locale' => 'fr_BE'])] = $item['ATTRIBUTE_VALUE_FR'];
//            }
//
            if ($context['has_locale'] === false && $item['VALUE_NL']) {
                $output[$item['UID']] = $item['VALUE_NL'];
            }
        }

//        if (count($invalid_msgs) > 0) {
//            throw new InvalidItemException(implode(', ', $invalid_msgs), $output);
//        }

        return $output;
    }

    public function findSelectCode(string $code, string $value)
    {
        return $this->cachedReader->find(['attribute' => $code, 'label-nl_BE' => $value])->getItems()['code'] ?? null;
    }

    public function revert(array $item): array
    {
        return array_replace($this->header->getHeaders(), $item);
    }

    public function getName(): string
    {
        return 'as400/article-attributes/csv';
    }
}