<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\DateTimeAction;
use PHPUnit\Framework\TestCase;

class DateTimeActionTest extends TestCase
{
    public function testConvertDateTimeFormatValidCase()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '8',
            'dateTime' => '2023-10-18 18:30:00'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d H:i:s',
            'outputFormat' => 'l, F j, Y \a\t g:i a',
        ]);

        $this->assertEquals([
            'sku' => '8',
            'dateTime' => 'Wednesday, October 18, 2023 at 6:30 pm'
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertDateTimeFormatEmptyField()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '9',
            'dateTime' => ''
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d H:i:s',
            'outputFormat' => 'F j, Y, g:i a',
        ]);

        $this->assertEquals([
            'sku' => '9',
            'dateTime' => ''
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertDateTimeFormatMissingField()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '10',
            'name' => 'Product ABC'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d H:i:s',
            'outputFormat' => 'F j, Y, g:i a',
        ]);

        $this->assertEquals([
            'sku' => '10',
            'name' => 'Product ABC'
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertDateTimeFormatInvalidFormat()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '11',
            'dateTime' => '2023-10-18 18:30:00'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d H:i',
            'outputFormat' => 'F j, Y, g:i a',
        ]);

        $this->assertEquals($item,  $dateTimeAction->apply($item));
    }

    public function testConvertDateFormatValidCase()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '8',
            'dateTime' => '2023-10-18'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d',
            'outputFormat' => 'l, F j, Y',
        ]);

        $this->assertEquals([
            'sku' => '8',
            'dateTime' => 'Wednesday, October 18, 2023'
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertDateFormatEmptyField()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '9',
            'dateTime' => ''
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d',
            'outputFormat' => 'F j, Y',
        ]);

        $this->assertEquals([
            'sku' => '9',
            'dateTime' => ''
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertDateFormatMissingField()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '10',
            'name' => 'Product ABC'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d',
            'outputFormat' => 'F j, Y',
        ]);

        $this->assertEquals([
            'sku' => '10',
            'name' => 'Product ABC'
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertTimeFormatValidCase()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '12',
            'dateTime' => '18:30:00'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'H:i:s',
            'outputFormat' => 'g:i a',
        ]);

        $this->assertEquals([
            'sku' => '12',
            'dateTime' => '6:30 pm'
        ],  $dateTimeAction->apply($item));
    }

    public function testConvertMappedFormatValidCase()
    {
        $dateTimeAction = new DateTimeAction();

        $item = [
            'sku' => '12',
            'dateTime' => '2023-10-18 18:30:00'
        ];

        $dateTimeAction->setOptions([
            'field' => 'dateTime',
            'inputFormat' => 'Y-m-d H:i:s',
            'outputFormat' => 'COOKIE',
        ]);

        $this->assertEquals([
            'sku' => '12',
            'dateTime' => 'Wednesday, 18-Oct-2023 18:30:00 UTC'
        ],  $dateTimeAction->apply($item));
    }
}