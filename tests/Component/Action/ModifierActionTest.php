<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\ModifierAction;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Format\StringToBooleanFormat;
use Misery\Component\Modifier\ReplaceCharacterModifier;
use PHPUnit\Framework\TestCase;

class ModifierActionTest extends TestCase
{
    public function test_it_should_modify_multi_key_values_action(): void
    {
        $registry = new Registry('modifier');
        $formatRegistry = new Registry('format');

        $modifier = new ReplaceCharacterModifier();
        $registry->register($modifier::NAME, $modifier);
        $format = new ModifierAction($registry, $formatRegistry);

        $format->setOptions(
            [
                'modifier' => $modifier::NAME,
                'keys' => 'description,short_description',
                'characters' => ['á' => 'a', 'é' => 'e'],
            ]
        );

        $item = [
            'description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'short_description' => [
                'nl_BE' => 'áán ná be ééglé sam max.',
                'fr_BE' => 'áán ná be ééglé sam max FR',
            ],
            'another_description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'sku' => '1',
        ];

        $expected = [
            'description' => [
                'nl_BE' => 'aaaaaabeeeeee',
                'fr_BE' => 'aaaaaabeeeeee',
            ],
            'short_description' => [
                'nl_BE' => 'aan na be eegle sam max.',
                'fr_BE' => 'aan na be eegle sam max FR',
            ],
            'another_description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'sku' => '1',
        ];

        $this->assertEquals($expected, $format->apply($item));
    }

    public function test_it_should_modify_single_key_values_action(): void
    {
        $registry = new Registry('modifier');
        $formatRegistry = new Registry('format');

        $modifier = new ReplaceCharacterModifier();
        $registry->register($modifier::NAME, $modifier);
        $format = new ModifierAction($registry, $formatRegistry);

        $format->setOptions(
            [
                'modifier' => $modifier::NAME,
                'keys' => 'short_description',
                'characters' => ['á' => 'a', 'é' => 'e'],
            ]
        );

        $item = [
            'description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'short_description' => [
                'nl_BE' => 'áán ná be ééglé sam max.',
                'fr_BE' => 'áán ná be ééglé sam max FR',
            ],
            'another_description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'sku' => '1',
        ];

        $expected = [
            'description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'short_description' => [
                'nl_BE' => 'aan na be eegle sam max.',
                'fr_BE' => 'aan na be eegle sam max FR',
            ],
            'another_description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => 'áááááábéééééé',
            ],
            'sku' => '1',
        ];

        $this->assertEquals($expected, $format->apply($item));
    }

    public function test_it_should_modify_single_key_with_empty_values_action(): void
    {
        $registry = new Registry('modifier');
        $formatRegistry = new Registry('format');

        $modifier = new ReplaceCharacterModifier();
        $registry->register($modifier::NAME, $modifier);
        $format = new ModifierAction($registry, $formatRegistry);

        $format->setOptions(
            [
                'modifier' => $modifier::NAME,
                'keys' => 'description',
                'characters' => ['á' => 'a', 'é' => 'e'],
            ]
        );

        $item = [
            'description' => [
                'nl_BE' => 'áááááábéééééé',
                'fr_BE' => null,
            ],
            'sku' => '1',
        ];

        $expected = [
            'description' => [
                'nl_BE' => 'aaaaaabeeeeee',
                'fr_BE' => null,
            ],
            'sku' => '1',
        ];

        $this->assertEquals($expected, $format->apply($item));
    }

    public function test_it_should_format_a_boolean_action(): void
    {
        $registry = new Registry('modifier');
        $formatRegistry = new Registry('format');
        $formatter = new StringToBooleanFormat();
        $formatRegistry->register($formatter::NAME, $formatter);
        $modifier = new ReplaceCharacterModifier();
        $registry->register($modifier::NAME, $modifier);
        $action = new ModifierAction($registry, $formatRegistry);

        $action->setOptions(
            [
                'formatter' => 'boolean',
                'keys' => 'active',
                'true' => 'TRUE',
                'false' => 'FALSE',
            ]
        );

        $item = [
            'active' => true,
            'sku' => '1',
        ];

        $expected = [
            'active' => 'TRUE',
            'sku' => '1',
        ];

        $this->assertEquals($expected, $action->apply($item));

        $item = [
            'active' => false,
            'sku' => '1',
        ];

        $expected = [
            'active' => 'FALSE',
            'sku' => '1',
        ];

        $this->assertEquals($expected, $action->apply($item));
    }

    public function test_it_should_format_a_boolean_action_no_options(): void
    {
        $registry = new Registry('modifier');
        $formatRegistry = new Registry('format');
        $formatter = new StringToBooleanFormat();
        $formatRegistry->register($formatter::NAME, $formatter);
        $modifier = new ReplaceCharacterModifier();
        $registry->register($modifier::NAME, $modifier);
        $action = new ModifierAction($registry, $formatRegistry);

        $action->setOptions(
            [
                'formatter' => 'boolean',
                'keys' => 'active',
            ]
        );

        $item = [
            'active' => true,
            'sku' => '1',
        ];

        $expected = [
            'active' => '1',
            'sku' => '1',
        ];

        $this->assertEquals($expected, $action->apply($item));

        $item = [
            'active' => false,
            'sku' => '1',
        ];

        $expected = [
            'active' => '0',
            'sku' => '1',
        ];

        $this->assertEquals($expected, $action->apply($item));
    }
}