<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\DecodeSpecialModifier;
use Misery\Component\Modifier\ReplaceCharacterModifier;
use PHPUnit\Framework\TestCase;

class ReplaceCharacterModifierTest extends TestCase
{
    function test_it_should_replace_all_chars(): void
    {
        $modifier = new ReplaceCharacterModifier();
        $modifier->setOptions(['characters' => [
            'á' => 'a',
            'é' => 'e',
        ]]);

        $this->assertSame(
            'aaaaaabeeeeeeZRT.',
            $modifier->modify('áááááábééééééZRT.')
        );
    }

    function test_it_should_replace_all_multi_chars(): void
    {
        $modifier = new ReplaceCharacterModifier();
        $modifier->setOptions(['characters' => [
            'áé' => 'ae',
        ]]);

        $this->assertSame(
            'áááááaeéééééZRT.',
            $modifier->modify('ááááááééééééZRT.')
        );
    }

    function test_it_should_replace_no_chars(): void
    {
        $modifier = new ReplaceCharacterModifier();

        $this->assertSame(
            'áááááábééééééZRT.',
            $modifier->modify('áááááábééééééZRT.')
        );
    }
}