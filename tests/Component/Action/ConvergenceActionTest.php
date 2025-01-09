<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\ConvergenceAction;
use Misery\Component\Action\SetValueAction;
use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class ConvergenceActionTest extends TestCase
{
    public function test_it_should_convergence_fields(): void
    {
        $format = new ConvergenceAction();
        $format->setOptions([
            'store_field' => 'address_line',
            'fields' => ['street', 'city', 'state'],
            'value' => '1',
        ]);

        $item = [
            'name' => 'John',
            'family_name' => 'Doe',
            'age' => '20',
            'email' => 'john@example.com',
            'street' => '123 Main Street',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'country' => 'United States',
        ];

        $this->assertEquals([
            'name' => 'John',
            'family_name' => 'Doe',
            'age' => '20',
            'email' => 'john@example.com',
            'street' => '123 Main Street',
            'city' => 'Anytown',
            'state' => 'CA',
            'postal_code' => '12345',
            'country' => 'United States',
            'address_line' => 'street: 123 Main Street, city: Anytown, state: CA',
        ], $format->apply($item));
    }

    public function test_it_should_convergence_fields_std_data(): void
    {
        $format = new ConvergenceAction();
        $format->setOptions([
            'store_field' => 'address_line',
            'fields' => ['street', 'city', 'state'],
            'value' => '1',
        ]);

        $item = [
            'values|street' => [
                'matcher' => Matcher::create('street'),
                'scope' => null,
                'locale' => null,
                'data' => '123 Main Street',
            ],
            'values|city' => [
                'matcher' => Matcher::create('city'),
                'scope' => null,
                'locale' => null,
                'data' => 'Anytown',
            ],
            'values|state' => [
                'matcher' => Matcher::create('state'),
                'scope' => null,
                'locale' => null,
                'data' => 'CA',
            ],
        ];

        $this->assertEquals(
            'street: 123 Main Street, city: Anytown, state: CA',
            $format->apply($item)['address_line' ]
        );
    }
}