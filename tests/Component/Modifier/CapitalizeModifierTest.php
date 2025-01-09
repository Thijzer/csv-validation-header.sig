<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\CapitalizeModifier;
use PHPUnit\Framework\TestCase;

class CapitalizeModifierTest extends TestCase
{
    function test_it_should_capitalize_a_value(): void
    {
        $modifier = new CapitalizeModifier();

        $this->assertEquals('Mynewvalue', $modifier->modify('myNewValue'));
        $this->assertEquals('My_new-value', $modifier->modify('my_New-Value'));
        $this->assertEquals('My new value', $modifier->modify('my New Value'));

    }
}