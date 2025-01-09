<?php

namespace Tests\Misery\Component\Decoding;

use Misery\Component\Common\Registry\Registry;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Format\StringToIntFormat;
use Misery\Component\Format\StringToListFormat;
use Misery\Component\Modifier\NullifyEmptyStringModifier;
use Misery\Component\Reader\ItemCollection;
use PHPUnit\Framework\TestCase;

class DecodingTest extends TestCase
{
    private $items = [
        [
            'id' => 1,
            'first_name' => 'Gordie',
            'codes' => ['A','B','C','D'],
        ],
        [
            'id' => 2,
            'first_name' => 'Frans',
            'codes' => ['E','F','G'],
        ],
        [
            'id' => 3,
            'first_name' => 'Dolly',
            'codes' => '',
        ],
    ];

    public function test_encode_item(): void
    {
        $encoderFactory = new ItemDecoderFactory();

        $formatRegistry = new Registry('format');
        $formatRegistry
            ->register(StringToListFormat::NAME, new StringToListFormat())
            ->register(StringToIntFormat::NAME, new StringToIntFormat())
        ;

        $encoderFactory->addRegistry($formatRegistry);

        $decode = $encoderFactory->createItemDecoder([
            'encode' => [
                'id' => [
                    'integer' => []
                ],
                'codes' => [
                    'list' => []
                ],
            ]
        ]);

        $decodedItem = $decode->decode($this->items[0]);

        $this->assertSame($decodedItem, [
            'id' => '1',
            'first_name' => 'Gordie',
            'codes' => 'A,B,C,D',
        ]);
    }
}