<?php
declare(strict_types=1);

namespace Tests\Misery\Component\Converter\Akeneo\Csv;

use Misery\Component\Converter\Akeneo\Csv\AttributeOption;
use PHPUnit\Framework\TestCase;

class AttributeOptionTest extends TestCase
{
    public function testConvert()
    {
        $converter = new AttributeOption();
        $inputData = [
            'label-nl_BE' => 'Option 1',
            'attribute' => 'option_1',
            'code' => 'other_value',
        ];

        $expectedOutput = [
            'labels' => [
                'nl_BE' => 'Option 1',
            ],
            'attribute' => 'option_1',
            'code' => 'other_value',
        ];

        $this->assertEquals($expectedOutput, $converter->convert($inputData));
    }

    public function testRevert()
    {
        $converter = new AttributeOption();
        $inputData = [
            'labels' => [
                'nl_BE' => 'Option 1',
            ],
            'attribute' => 'option_1',
            'code' => 'other_value',
        ];

        $expectedOutput = [
            'label-nl_BE' => 'Option 1',
            'attribute' => 'option_1',
            'code' => 'other_value',
        ];

        $this->assertEquals($expectedOutput, $converter->revert($inputData));
    }

    public function testName()
    {
        $converter = new AttributeOption();
        $this->assertEquals('akeneo/attribute_options/csv', $converter->getName());
    }
}
