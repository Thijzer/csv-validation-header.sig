<?php

namespace Misery\Component\Action;

use Misery\Component\Akeneo\AkeneoValuePicker;
use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderAwareInterface;
use Misery\Component\Reader\ItemReaderAwareTrait;

// TODO we are stuck on 'code' source -> getReference or SourceRepository in favor of reader are other options

class ReplaceAction implements OptionsInterface, ItemReaderAwareInterface
{
    use OptionsTrait;
    use ItemReaderAwareTrait;
    private $repo;
    private $prepReader;

    public const NAME = 'replace';

    /** @var array */
    private $options = [
        'method' => null,
        'source' => null,
        'source_filter' => [],
        'source_reference' => 'code',
        'key' => null,
        'list' => null,
        'set' => [],
        'format' => '[%s]',
        'content' => 'label',
        'locale' => null,
        'locales' => null,
    ];

    public function apply(array $item): array
    {
        if ($set = $this->getOption('set')) {
            return array_replace_recursive($item, $set);
        }

        if (array_key_exists($this->options['key'], $item)) {
            $label = $this->options['content'];

            switch ($this->options['method']) {

                case "getLabel":
                    $sourceItem = $this->getItem($listItem = $item[$this->options['key']]);
                    $item[$this->options['key']] = $sourceItem ? AkeneoValuePicker::pick($sourceItem, $label, $this->options) : $this->format($listItem);
                    break;

                case "getLabels":
                    $sourceItem = $this->getItem($listItem = $item[$this->options['key']]);
                    $tmp = [];
                    foreach ($this->options['locales'] as $locale) {
                        $tmp[$locale] = $sourceItem ? AkeneoValuePicker::pick($sourceItem, $label, ['locale' => $locale]) : $this->format($listItem);
                    }
                    $item[$this->options['key']] = $tmp;
                    break;

                case "getLabelFromList":
                    $tmp = [];
                    foreach ($item[$this->options['key']] ?? [] as $key => $listItem) {
                        $sourceItem = $this->getItem($listItem);
                        $tmp[$key] = $sourceItem ? AkeneoValuePicker::pick($sourceItem, $label, $this->options) : $this->format($listItem);
                    }

                    $item[$this->options['key']] = $tmp;
                    break;

                case "getLabelsFromList":
                    $tmp = [];
                    foreach ($this->options['locales'] as $locale) {
                        $tmp[$locale] = [];
                        foreach ($item[$this->options['key']] ?? [] as $listItem) {
                            $sourceItem = $this->getItem($listItem);
                            $tmp[$locale][] = $sourceItem ? AkeneoValuePicker::pick($sourceItem, $label, ['locale' => $locale]) : $this->format($listItem);
                        }
                    }
                    $item[$this->options['key']] = $tmp;
                    break;

                default;
                    break;
            }
        }

        return $item;
    }

    private function format(string $item = null): ?string
    {
        return $item && $this->options['format'] ? sprintf($this->options['format'], $item) : null;
    }

    private function getSource()
    {
        // this is a tmp performance improvement
        // todo create a in de Source Collection named filters per source,
        // app-example de filter name attribute_option/attribute:brand is a subset collection of attribute_option where attribute = brand
        // this will effect the find performance method that no longer needs to loop the whole attribute_option file.
        // these named filters should be stored automatically as collections per Source.
        if (null === $this->prepReader) {
            $this->prepReader = $this->getReader();
            if (!empty($this->options['source_filter'])) {
                $this->prepReader = new ItemReader(
                    new ItemCollection(
                        $this->prepReader->find($this->options['source_filter'])->getItems()
                    )
                );
            }
        }

        return $this->prepReader;
    }

    private function getItem($reference)
    {
        if (null === $reference) {
            return null;
        }

        // @todo make a prep reader

        return $this->getSource()
            ->find([$this->options['source_reference'] => $reference])
            ->getIterator()
            ->current()
        ;
    }
}