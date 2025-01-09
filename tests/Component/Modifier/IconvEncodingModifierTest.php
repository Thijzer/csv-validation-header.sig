<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\IconvEncodingModifier;
use PHPUnit\Framework\TestCase;

class IconvEncodingModifierTest extends TestCase
{
    /** @group performance */
    function test_it_should_encode_values_with_iconv(): void
    {
        $modifier = new IconvEncodingModifier();
        if ($modifier->supports()) {
            $modifier->setOptions(['out_charset' => 'ascii//TRANSLIT']);

            $this->assertEquals('F?? B?r', $modifier->modify('Fóø Bår'));
        }
    }
}