<?php

namespace Tests\Misery\Component\Statement;

use Misery\Component\Action\ListMapperAction;
use Misery\Component\Action\SetValueAction;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Statement\ContainsStatement;
use Misery\Component\Statement\EqualsStatement;
use Misery\Component\Statement\StatementFactory;
use Misery\Component\Statement\StatementInterface;
use PHPUnit\Framework\TestCase;

class StatementFactoryTest extends TestCase
{
    private function setUpFactory(): StatementFactory
    {
        $registry = new Registry('statement');
        $registry->register(EqualsStatement::NAME, EqualsStatement::class);
        $registry->register(ContainsStatement::NAME, ContainsStatement::class);
        $actionRegistry = new Registry('action');
        $actionRegistry->register(SetValueAction::NAME, new SetValueAction());
        $actionRegistry->register(ListMapperAction::NAME, new ListMapperAction());

        $factory = new StatementFactory();
        $factory->addRegistry($registry);
        $factory->addRegistry($actionRegistry);

        return $factory;
    }

    public function test_it_should_set_statements_from_configuration(): void
    {
        $factory = $this->setUpFactory();

        $configuration = [
            [
                'name' => 'test 1',
                'when' => [
                    'field' => 'brand',
                    'operator' => 'EQUALS',
                    'state' => 'louis',
                ],
                'then' => [
                    'action' => 'set',
                    'field' => 'brand',
                    'state' => 'Louis Vuitton',
                ],
            ]
        ];

        /** @var StatementInterface $collection */
        $collection = $factory->createFromConfiguration($configuration);

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'LV',
            'sku' => '1234678',
        ], $collection->apply($item));
    }

    public function test_it_should_a_set_of_statements_from_configuration(): void
    {
        $factory = $this->setUpFactory();

        $configuration = [
            [
                'name' => 'test 1',
                'when' => [
                    'field' => 'brand',
                    'operator' => 'EQUALS',
                    'state' => 'louis',
                ],
                'then' => [
                    'action' => 'set',
                    'field' => 'brand',
                    'state' => 'Louis Vuitton',
                ],
            ],
            [
                'name' => 'test 2',
                'when' => [
                    'field' => 'sku',
                    'operator' => 'CONTAINS',
                    'state' => 'LV',
                ],
                'then' => [
                    'action' => 'set',
                    'field' => 'description',
                    'state' => 'new Louis',
                ],
            ]
        ];

        /** @var StatementInterface $collection */
        $collection = $factory->createFromConfiguration($configuration);

        $item = [
            'brand' => 'louis',
            'description' => 'LV',
            'sku' => 'LV-1234678',
        ];

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'description' => 'new Louis',
            'sku' => 'LV-1234678',
        ], $collection->apply($item));

        $item = [
            'brand' => 'Diesel',
            'description' => 'D',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'Diesel',
            'description' => 'D',
            'sku' => '1234678',
        ], $collection->apply($item));
    }

    public function test_it_should_a_set_of_statements_from_with_list_configuration(): void
    {
        $factory = $this->setUpFactory();

        $configuration = [
            [
                'name' => 'test 1',
                'when' => [
                    'field' => 'family',
                    'operator' => 'EQUALS',
                    'state' => 'bags',
                ],
                'then' => [
                    'action' => 'value_mapping_in_list',
                    'field' => 'brand',
                ],
                'context' => [
                    'list' => [
                        'louis' => 'Louis Vuitton',
                        'diesel' => 'Diesel nv.',
                    ]
                ]
            ],
        ];

        /** @var StatementInterface $collection */
        $collection = $factory->createFromConfiguration($configuration);

        $item = [
            'brand' => 'louis',
            'family' => 'bags',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'Louis Vuitton',
            'family' => 'bags',
            'sku' => '1234678',
        ], $collection->apply($item));

        $item = [
            'brand' => 'diesel',
            'family' => 'shirts',
            'sku' => '1234678',
        ];

        $this->assertEquals([
            'brand' => 'diesel',
            'family' => 'shirts',
            'sku' => '1234678',
        ], $collection->apply($item));
    }
}