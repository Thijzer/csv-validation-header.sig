<?php

namespace Tests\Misery\Component\Common\Replacer;

use Misery\Component\Common\Picker\StandardValuePicker;
use Misery\Component\Common\Replacer\StandardValueReplacer;
use PHPUnit\Framework\TestCase;

class StandardValueReplacerTest extends TestCase
{
    private $item = [
        'values' => [
            "variation_name" =>[
                0 => [
                    "data" => "",
                    "locale" => "de_DE",
                    "scope" => null,
                    "key" => "variation_name-de_DE",
                ],
                1 => [
                    "data" => "",
                    "locale" => "en_US",
                    "scope" => null,
                    "key" => "variation_name-en_US",
                ],
                2 =>  [
                    "data" => "",
                    "locale" => "fr_FR",
                    "scope" => null,
                    "key" => "variation_name-fr_FR",
                ],
            ],
            "wash_temperature" => [
                0 =>  [
                    "data" => "",
                    "locale" => null,
                    "scope" => null,
                    "key" => "wash_temperature",
                ]
            ],
            "short_description" => [
                0 => [
                    "data" => "ecommerce-de_DE",
                    "locale" => "de_DE",
                    "scope" => "ecommerce",
                    "key" => "short_description-de_DE-ecommerce",
                ],
                1 => [
                    "data" => "ecommerce-en_US",
                    "locale" => "en_US",
                    "scope" => "ecommerce",
                    "key" => "short_description-en_US-ecommerce",
                ],
                2 => [
                    "data" => "ecommerce-fr_FR",
                    "locale" => "fr_FR",
                    "scope" => "ecommerce",
                    "key" => "short_description-fr_FR-ecommerce",
                ],
                3 => [
                    "data" => "print-de_DE",
                    "locale" => "de_DE",
                    "scope" => "print",
                    "key" => "short_description-de_DE-print",
                ],
                4 => [
                    "data" => "print-en_US",
                    "locale" => "en_US",
                    "scope" => "print",
                    "key" => "short_description-en_US-print",
                ],
                5 => [
                    "data" => "print-fr_FR",
                    "locale" => "fr_FR",
                    "scope" => "print",
                    "key" => "short_description-fr_FR-print",
                ],
            ],
            "price" => [
                0 => [
                    "data" => "",
                    "locale" => null,
                    "scope" => null,
                    "currency" => "EUR",
                    "key" => "price-EUR",
                ],
                1 => [
                    "data" => "",
                    "locale" => null,
                    "scope" => null,
                    "currency" => "USD",
                    "key" => "price-USD",
                ]
            ],
            "weight" => [
                0 => [
                    "data" => "500",
                    "locale" => null,
                    "scope" => null,
                    "key" => "weight",
                    "unit" => "GRAM",
                ],
            ],
            "keywords" => [
                0 => [
                    "data" => "",
                    "locale" => null,
                    "scope" => 'print',
                    "key" => "keywords-print",
                ],
                1 => [
                    "data" => "",
                    "locale" => null,
                    "scope" => 'ecommerce',
                    "key" => "keywords-ecommerce",
                ]
            ],
        ],
    ];

    public function test_replace_a_context_value(): void
    {
        $item = StandardValueReplacer::replace($this->item, "short_description", function($value) {
            return 'SHEEP';
        }, ['locale' => 'de_DE', 'scope' => 'print']);

        $this->assertSame([
            0 => [
                "data" => "ecommerce-de_DE",
                "locale" => "de_DE",
                "scope" => "ecommerce",
                "key" => "short_description-de_DE-ecommerce",
            ],
            1 => [
                "data" => "ecommerce-en_US",
                "locale" => "en_US",
                "scope" => "ecommerce",
                "key" => "short_description-en_US-ecommerce",
            ],
            2 => [
                "data" => "ecommerce-fr_FR",
                "locale" => "fr_FR",
                "scope" => "ecommerce",
                "key" => "short_description-fr_FR-ecommerce",
            ],
            3 => [
                "data" => "SHEEP",
                "locale" => "de_DE",
                "scope" => "print",
                "key" => "short_description-de_DE-print",
            ],
            4 => [
                "data" => "print-en_US",
                "locale" => "en_US",
                "scope" => "print",
                "key" => "short_description-en_US-print",
            ],
            5 => [
                "data" => "print-fr_FR",
                "locale" => "fr_FR",
                "scope" => "print",
                "key" => "short_description-fr_FR-print",
            ],
        ], $item['values']['short_description']);
    }
}