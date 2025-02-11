<?php

namespace Tests\Misery\Component\Common\Utils;

use Misery\Component\Common\Utils\ValueFormatter;
use PHPUnit\Framework\TestCase;

class ValueFormatterTest extends TestCase
{
    public function testSingleFormat()
    {
        $format = '%amount% %unit%';
        $values = ['amount' => 1, 'unit' => 'GRAM'];
        $expectedResult = '1 GRAM';

        $result = ValueFormatter::format($format, $values);

        $this->assertEquals($expectedResult, $result);
    }

    public function testSingleFormatWithEmptyValue()
    {
        $format = '%amount% %unit%';
        $values = ['amount' => 1, 'unit' => ''];
        $expectedResult = '1 ';

        $result = ValueFormatter::format($format, $values);

        $this->assertEquals($expectedResult, $result);
    }

    public function testMultiFormat()
    {
        $formats = ['%amount% %unit%', 'Value: %value%'];
        $values = ['amount' => 1, 'unit' => 'GRAM', 'value' => 42];
        $expectedResults = ['1 GRAM', 'Value: 42'];

        $result = ValueFormatter::formatMulti($formats, $values);

        $this->assertEquals($expectedResults, $result);
    }

    public function testMultiFormatWithEmptyValue()
    {
        $formats = ['%amount% %unit%', 'Value: %value%'];
        $values = ['amount' => 1, 'unit' => 'GRAM', 'value' => ''];
        $expectedResults = ['1 GRAM', 'Value: '];

        $result = ValueFormatter::formatMulti($formats, $values);

        $this->assertEquals($expectedResults, $result);
    }

    public function testRecursiveFormatWithNestedArray()
    {
        $format = '%amount% %unit% %box%';
        $values = [
            'amount' => 1,
            'details' => [
                'key1' => 'value1',
                'unit' => 'METER',
            ],
            'values' => [
                'boxes' => [
                    'box' => '5',
                ]
            ]
        ];
        $expectedResult = '1 METER 5';

        $result = ValueFormatter::recursiveFormat($format, $values);

        $this->assertEquals($expectedResult, $result);
    }
}
