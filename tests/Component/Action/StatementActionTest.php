<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\StatementAction;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use PHPUnit\Framework\TestCase;

class StatementActionTest extends TestCase
{
    public function test_it_should_do_a_statement_with_copy_action_and_key_pair_list(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'new_brand' => '',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'IN_LIST',
                'context' => [
                    'list' => [
                        'louis' => null,
                        'nike' => null,
                        'reebok' => null,
                    ],
                ],
            ],
            'then'  => [
                'action' => 'copy',
                'from' => 'brand',
                'to' => 'new_brand',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'new_brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_statement_with_copy_action_and_list(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'new_brand' => '',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'IN_LIST',
                'context' => [
                    'list' => [
                        'louis',
                        'nike',
                        'reebok',
                    ],
                ],
            ],
            'then'  => [
                'action' => 'copy',
                'from' => 'brand',
                'to' => 'new_brand',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'louis',
            'new_brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_statement_with_set_action_with_list(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'IN_LIST',
                'context' => [
                    'list' => [
                        'louis',
                        'nike',
                        'reebok',
                    ],
                ],
            ],
            'then'  => [
                'field' => 'brand',
                'state' => 'Louis',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'Louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_statement_with_set_action(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'EQUALS',
                'state' => 'louis',
            ],
            'then'  => [
                'field' => 'brand',
                'state' => 'Louis',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'Louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_statement_with_key_value_set_action(): void
    {
        $format = new StatementAction();

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'brand',
                'operator' => 'EQUALS',
                'state' => 'louis',
            ],
            'then' => [
                'brand' => 'Louis',
            ],
        ]);

        $this->assertEquals([
            'brand' => 'Louis',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_skip_when_char_length_is_greater_then_check_value(): void
    {
        $this->expectException(SkipPipeLineException::class);

        $format = new StatementAction();

        $item = [
            'fr_values' => [
                'skcsveysvmtpkiddxdjwugeayqvruotxzgffqwkrkhbxjldyfxfqfmzwakqdixikxxprbfpscldluxibczlalgfxkaitnikgyrlkvweoeahctrjjkdgkeoxfnmfjmmyancrldbupzxvvvsgcdwiphlxhwqslhybzzegsyuuyxqgvesciybykosbozznteeaxctgqbgfkyzoveopmkhwkezzliegitxkokrbpjyiatroopyspndjnhgznvfnyyiultqivpeg',
            ],
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'fr_values',
                'operator' => 'CHARLEN_GT',
                'context' => [
                    'char_len' => 255
                ],
            ],
            'then' => [
                'action' => 'skip',
            ],
        ]);
        $format->apply($item);
    }

    public function test_it_should_NOT_skip_when_char_length_is_greater_then_check_value(): void
    {
        $format = new StatementAction();

        $item = [
            'fr_values' => [
                "110 V AC : 50 Hz, puissance d'appel 5,0 VA, puissance de maintien 3,7 VA;;110 V AC : 60 Hz, puissance d'appel 5,0 VA, puissance de maintien 3,7 VA;;230 V AC : 50 Hz, puissance d'appel 5,0 VA, puissance de maintien 3,7 VA;;230 V AC : 60 Hz, ",
            ],
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => [
                'field' => 'fr_values',
                'operator' => 'CHARLEN_GT',
                'context' => [
                    'char_len' => 255
                ],
            ],
            'then' => [
                'action' => 'skip',
            ],
        ]);
        $format->apply($item);

        $this->assertEquals($item, $format->apply($item));
    }

    public function test_it_should_compare_with_different_operators(): void
    {
        $format = new StatementAction();

        $item = [
            'correct' => 'FALSE',
            'number_high' => '10',
            'number_low' => '1',
            'number_calc' => '10',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => '{number_high} GREATER_THAN {number_low}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} GREATER_THAN {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('FALSE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_high} GREATER_THAN_OR_EQUAL_TO {number_calc}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_high} <= {number_calc}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} < {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_high} > {number_low}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} > {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('FALSE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_calc} >= {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} <= {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_calc} == {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} < {number_high} AND {number_calc} == {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} != {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);

        $format->setOptions([
            'when' => '{number_low} == {number_high}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('FALSE', $format->apply($item)['correct']);
    }

    public function test_it_should_compare_with_different_operators_with_weird_chars(): void
    {
        $format = new StatementAction();

        $item = [
            'correct' => 'FALSE',
            'Number-high.A_*' => '10',
            'Number-low.A_*' => '1',
            'number_calc' => '10',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => '{Number-high.A_*} GREATER_THAN {Number-low.A_*}',
            'then' => [
                'correct' => 'TRUE',
            ],
        ]);
        $this->assertSame('TRUE', $format->apply($item)['correct']);
    }

    public function test_it_should_support_other_data_types(): void
    {
        $format = new StatementAction();

        $item = [
            'correct' => null,
            'A' => '10',
            'sku' => '1',
        ];

        $format->setOptions([
            'when' => 'A NOT_EMPTY',
            'then' => [
                'correct' => true,
            ],
        ]);

        $this->assertTrue($format->apply($item)['correct']);

        $format->setOptions([
            'when' => 'A NOT_EMPTY',
            'then' => [
                'correct' => 2.3456,
            ],
        ]);

        $this->assertSame(2.3456, $format->apply($item)['correct']);


        $format->setOptions([
            'when' => 'A NOT_EMPTY',
            'then' => [
                'correct' => ['AO'],
            ],
        ]);

        $this->assertSame(['AO'], $format->apply($item)['correct']);
    }
}