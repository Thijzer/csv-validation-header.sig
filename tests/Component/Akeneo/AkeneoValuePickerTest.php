<?php


namespace Tests\Misery\Component\Akeneo;

use Misery\Component\Akeneo\AkeneoValuePicker;
use PHPUnit\Framework\TestCase;

class AkeneoValuePickerTest extends TestCase
{
    private $item = [
        'brand' => 'louis',
        'description' => [
            'pim' => [
                'nl_BE' => 'LVS',
            ],
        ],
        'short_description' => [
            'nl_BE' => 'LV',
        ],
        'needs_to_exported' => [
            'pim' => 'true',
        ],
        'sku' => '1',
    ];

    public function test_it_should_pick_a_global_value(): void
    {
        $this->assertSame(
            'louis', AkeneoValuePicker::pick($this->item, $key = 'brand')
        );

        $this->assertSame(
            null, AkeneoValuePicker::pick($this->item, $key = 'brand', ['locale' => 'nl_BE'])
        );

        $this->assertSame(
            null, AkeneoValuePicker::pick($this->item, $key = 'brand', ['locale' => 'nl_BE', 'scope' => 'pim'])
        );
    }

    public function test_it_should_pick_a_local_value(): void
    {
        $this->assertIsArray(
            AkeneoValuePicker::pick($this->item, $key = 'short_description')
        );

        $this->assertSame(
            'LV', AkeneoValuePicker::pick($this->item, $key = 'short_description', ['locale' => 'nl_BE'])
        );

        $this->assertSame(
            'LV', AkeneoValuePicker::pick($this->item, $key = 'short_description', ['locale' => 'nl_BE', 'scope' => 'pim'])
        );
    }

    public function test_it_should_pick_a_scope_value(): void
    {
        $this->assertIsArray(
             AkeneoValuePicker::pick($this->item, $key = 'needs_to_exported')
        );

        $this->assertSame(
            'true', AkeneoValuePicker::pick($this->item, $key = 'needs_to_exported', ['scope' => 'pim'])
        );

        $this->assertSame(
            'true', AkeneoValuePicker::pick($this->item, $key = 'needs_to_exported', ['locale' => 'nl_BE', 'scope' => 'pim'])
        );
    }

    public function test_it_should_pick_a_local_and_scope_value(): void
    {
        $this->assertIsArray(
             AkeneoValuePicker::pick($this->item, $key = 'description')
        );

        $this->assertSame(
            'LVS', AkeneoValuePicker::pick($this->item, $key = 'description', ['locale' => 'nl_BE', 'scope' => 'pim'])
        );

        $this->assertSame(
            ['nl_BE' => 'LVS'], AkeneoValuePicker::pick($this->item, $key = 'description', ['scope' => 'pim'])
        );
    }
}