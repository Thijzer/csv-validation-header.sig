<?php

namespace Tests\Misery\Component\AttributeFormatter;

use Induxx\Twig\Extension\TranslationBundleExtension;
use Misery\Component\AttributeFormatter\BooleanAttributeFormatter;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
class BooleanAttributeFormatterTest extends TestCase
{
    use ProphecyTrait;

    public function test_it_should_boolean_type_a_value(): void
    {
        $format = new BooleanAttributeFormatter();

        $context = [
            'label' => [
                'Y' => 'YES',
                'N' => 'NO',
            ],
            'current-attribute-type' => 'pim_catalog_boolean',
        ];

        $this->assertSame($format->format(true, $context), 'YES');
        $this->assertSame($format->format(false, $context), 'NO');

        $this->assertTrue($format->requires($context));
        $this->assertTrue($format->supports('pim_catalog_boolean'));

        $context = [
            'label' => [
                'Y' => 'Ja',
                'N' => 'Nee',
            ],
            'current-attribute-type' => 'pim_catalog_boolean',
        ];

        $this->assertSame($format->format(true, $context), 'Ja');
        $this->assertSame($format->format(false, $context), 'Nee');
    }
}