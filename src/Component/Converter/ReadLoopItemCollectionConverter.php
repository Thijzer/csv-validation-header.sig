<?php

namespace Misery\Component\Converter;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Modifier\ArrayFlattenModifier;
use Misery\Component\Modifier\ArrayUnflattenModifier;
use Misery\Component\Modifier\NullifyEmptyStringModifier;
use Misery\Component\Reader\ItemCollection;

class ReadLoopItemCollectionConverter implements InitConverterInterface, ConverterInterface, ItemCollectionLoaderInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private array $options = [
        'loop_item' => null,
        'join_with' => [],
        'properties' => [],
        'parse' => [],
    ];
    private ItemEncoder $encoder;

    public function init(): void
    {
        $this->encoder = $this->ItemEncoderDecoderFactory()->createItemEncoder([
            'encode' => $this->getOption('properties'),
            'parse' => $this->getOption('parse'),
        ]);
    }

    private function ItemEncoderDecoderFactory(): ItemEncoderFactory
    {
        $encoderFactory = new ItemEncoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToBooleanFormat::NAME, new StringToBooleanFormat())
        ;
        $modifierRegistry = new Registry('modifier');
        $modifierRegistry
            ->register(NullifyEmptyStringModifier::NAME, new NullifyEmptyStringModifier())
            ->register(ArrayUnflattenModifier::NAME, new ArrayUnflattenModifier())
            ->register(ArrayFlattenModifier::NAME, new ArrayFlattenModifier())
        ;
        $encoderFactory->addRegistry($formatRegistry);
        $encoderFactory->addRegistry($modifierRegistry);

        return $encoderFactory;
    }

    public function load(array $item): ItemCollection
    {
        return new ItemCollection($this->convert($item));
    }

    public function convert(array $item): array
    {
        if (
            $this->getOption('parse') !== [] ||
            $this->getOption('properties') !== []
        ) {
            $item = $this->encoder->encode($item);
        }

        if (
            isset($item[$this->getOption('loop_item')]) &&
            is_array($item[$this->getOption('loop_item')])
        ) {
            $rootItem = [];
            if ($this->getOption('join_with') !== []) {
                $rootItem = ColumnReducer::reduceItem($item, ...$this->getOption('join_with'));
            }

            $result = [];
            foreach ($item[$this->getOption('loop_item')] as $i => $distilledItem) {
                $result[$i] = array_merge($rootItem, $distilledItem);
            }

            return $result;
        }

        return $item;
    }

    public function getName(): string
    {
        return 'read/loop/items';
    }

    public function revert(array $item): array
    {
        throw new \Exception(ReadLoopItemCollectionConverter::class . ' is not revert-able.');
    }
}