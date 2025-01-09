<?php

namespace Tests\Misery\Component\Functions;

use Misery\Component\Common\Cursor\CachedCursor;
use Misery\Component\Common\Cursor\FunctionalCursor;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Tracker\TimeTracker;
use Misery\Component\Parser\CsvParser;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class ArrayFunctionsTest extends TestCase
{
    private $item = [
        'id' => '1',
        'user' => ['first_name' => 'Simon'],
    ];

    public function test_function_flatten_values(): void
    {
        $result = ArrayFunctions::flatten($this->item);

        $expected = [
            'id' => '1',
            'user.first_name' => 'Simon',
        ];

        $this->assertSame($expected, $result);
    }

    public function test_function_unflatten_values(): void
    {
        $item = [
            'id' => '1',
            'user.first_name' => 'Simon',
        ];

        $result = ArrayFunctions::unflatten($item);

        $this->assertSame($this->item, $result);
    }

    public function test_impossible_values(): void
    {
        $item = [
            'id' => '1',
            'id.first_name' => 'Simon',
        ];

        $result = ArrayFunctions::unflatten($item);

        $expected = [
            'id' => [
                '' => '1',
                'first_name' => 'Simon',
            ],
        ];

        $this->assertSame($expected, $result);

        $result = ArrayFunctions::flatten($expected);

        $this->assertSame($item, $result);
    }

    /** @group performance */
    public function test_flatten_performance(): void
    {
        $file = new \SplFileObject(__DIR__ . '/../../examples/users.csv');
        $reader = new ItemReader(new CachedCursor(new FunctionalCursor(new CsvParser($file, ';'), function($item)  {
            return ArrayFunctions::unflatten($item);
        })));

        // approx 300.000 lines test
        $tracker = new TimeTracker();
        foreach (range(1, 1000) as $i) {
            foreach ($reader->getIterator() as $item) {
                ArrayFunctions::flatten($item);
            }
        }
        $this->assertLessThan(5, $check = $tracker->check());

        print_r('flatten:'.$check);
    }

    /** @group performance */
    public function test_unflatten_performance(): void
    {
        $file = new \SplFileObject(__DIR__ . '/../../examples/users.csv');
        $reader = new ItemReader(new CachedCursor(new CsvParser($file, ';')));

        // approx 300.000 lines test
        $tracker = new TimeTracker();
        foreach (range(1, 1000) as $i) {
            foreach ($reader->getIterator() as $item) {
                ArrayFunctions::unflatten($item);
            }
        }
        $this->assertLessThan(12, $check = $tracker->check());

        print_r('unflatten:'.$check);
    }

    public function test_it_should_flatten_list_types(): void
    {
        $item = [
            'brand' => [
                'nl_BE' => [],
                'fr_BE' => [],
                'en_US' => [],
            ],
            'description' => 'LV',
            'sku' => '1',
        ];

        $result = ArrayFunctions::flatten($item);

        $this->assertSame([
            'brand.nl_BE' => [],
            'brand.fr_BE' => [],
            'brand.en_US' => [],
            'description' => 'LV',
            'sku' => '1',
        ], $result);
    }


    public function test_it_should_flatten_with_a_target_list(): void
    {
        $item = [
            'brand' => [
                'nl_BE' => [],
                'fr_BE' => [],
                'en_US' => [],
            ],
            'supplier_brand' => [
                'nl_BE' => [],
                'fr_BE' => [],
                'en_US' => [],
            ],
            'description' => 'LV',
            'sku' => '1',
        ];

        $result = ArrayFunctions::flatten($item, '.', '', ['supplier_brand']);

        $this->assertSame([
            'brand' => [
                'nl_BE' => [],
                'fr_BE' => [],
                'en_US' => [],
            ],
            'supplier_brand.nl_BE' => [],
            'supplier_brand.fr_BE' => [],
            'supplier_brand.en_US' => [],
            'description' => 'LV',
            'sku' => '1',
        ], $result);
    }

    public function test_it_should_flatten_with_preserve_integer_keys_on(): void
    {
        $item = [
            'product' => [
                'values' => [
                    [
                        'brand' => 'my_value',
                    ]
                ],
                'brand_labels' => [
                    'nl_BE' => [],
                    'fr_BE' => [],
                    'en_US' => [],
                ],
                'description' => 'LV',
                'sku' => '1',
            ]
        ];

        $result = ArrayFunctions::flatten($item, '.', '', [], $preserveIntKeys = true);

        $this->assertSame([
            'product.values' => [
                [
                    'brand' => 'my_value',
                ]
            ],
            'product.values.0.brand' =>  'my_value',
            'product.brand_labels.nl_BE' => [],
            'product.brand_labels.fr_BE' => [],
            'product.brand_labels.en_US' => [],
            'product.description' => 'LV',
            'product.sku' => '1',
        ], $result);
    }

    public function test_it_should_strpos_any_elem_from_an_array(): void
    {
        $needles = ['burger', 'town'];

        $this->assertFalse(ArrayFunctions::strpos_array('String contains word "cheese" and "tea".', $needles));

        $this->assertTrue(ArrayFunctions::strpos_array('String contains word "cheese" and "town".', $needles));
    }
}