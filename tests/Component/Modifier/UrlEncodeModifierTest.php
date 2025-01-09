<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\DecodeSpecialModifier;
use Misery\Component\Modifier\ReplaceCharacterModifier;
use Misery\Component\Modifier\UrlEncodeModifier;
use PHPUnit\Framework\TestCase;

class UrlEncodeModifierTest extends TestCase
{
    function test_it_should_url_encode(): void
    {
        $modifier = new UrlEncodeModifier();

        $this->assertSame(
            'foo%20%40%2B%25%2F.',
            $modifier->modify('foo @+%/.')
        );
    }
}