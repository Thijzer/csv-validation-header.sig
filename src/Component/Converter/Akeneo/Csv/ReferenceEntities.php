<?php
declare(strict_types=1);

namespace Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\InitConverterInterface;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Mapping\ColumnMapper;
use Misery\Component\Modifier\ArrayFlattenModifier;
use Misery\Component\Modifier\ArrayUnflattenModifier;
use Misery\Component\Modifier\NullifyEmptyStringModifier;

class ReferenceEntities implements ConverterInterface, InitConverterInterface, RegisteredByNameInterface, OptionsInterface
{
    use OptionsTrait;

    private ?ItemEncoder $encoder = null;
    private ItemDecoder $decoder;
    private ColumnMapper $mapper;

    private $options = [
        'identifier' => 'code',
        'reference-code' => null,
        'properties' => [
            'code' => [
                'text' => null,
            ],
        ],
        'field_mapping' => [],
        'parse' => [
            'unflatten' => [
                'separator' => '-',
            ],
            'nullify' => null,
        ],
    ];

    public function init(): void
    {
        if (!$this->encoder) {
            list($encoder, $decoder) = $this->ItemEncoderDecoderFactory();
            $this->encoder = $encoder->createItemEncoder([
                'encode' => $this->getOption('properties'),
                'parse' => $this->getOption('parse'),
            ]);
            $this->decoder = $decoder->createItemDecoder([
                'decode' => $this->getOption('properties'),
                'parse' => $this->getOption('parse'),
            ]);
            $this->mapper = new ColumnMapper();
        }
    }

    public function convert(array $item): array
    {
        $this->init();
        $item = $this->encoder->encode($item);

        if ($fm = $this->getOption('field_mapping')) {
            $item = $this->mapper->map($item, $fm);
        }
        if ($id = $this->getOption('reference-code')) {
            $item['reference-code'] = $id;
        }

        return $item;
    }

    public function revert(array $item): array
    {
        $this->init();
        $item = $this->decoder->decode($item);

        return $item;
    }

    private function ItemEncoderDecoderFactory(): array
    {
        $encoderFactory = new ItemEncoderFactory();
        $decoderFactory = new ItemDecoderFactory();

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

        $decoderFactory->addRegistry($formatRegistry);
        $decoderFactory->addRegistry($modifierRegistry);

        return [$encoderFactory, $decoderFactory];
    }

    public function getName(): string
    {
        return 'akeneo/reference_entities/csv';
    }
}
