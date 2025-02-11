<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\ReplaceAction;
use Misery\Component\Common\Repository\ItemRepository;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use PHPUnit\Framework\TestCase;

class ReplaceActionTest extends TestCase
{
    private $brands = [
        [
            'code' => '_nike_',
            'label' => [
                'nl_BE' => 'Nike',
                'fr_BE' => 'Nikell',
            ],
        ],
        [
            'code' => '_adi_',
            'label' => [
                'nl_BE' => 'Adidas',
                'fr_BE' => 'Adieu',
            ],
        ],
        [
            'code' => '_newb_',
            'label' => [
                'nl_BE' => 'new',
                'fr_BE' => null,
            ],
        ],
    ];

    private function getReader(): ItemReader
    {
        return new ItemReader(new ItemCollection($this->brands));
    }

    public function test_it_should_do_a_replace_a_label_action(): void
    {
        $format = new ReplaceAction();
        $format->setReader($this->getReader());

        $item = [
            'brand' => '_nike_',
            'description' => 'LV',
            'sku' => '1',
        ];

        $format->setOptions([
            'method' => 'getLabel',
           # 'source' => 'brands',
            'locale' => 'nl_BE',
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => 'Nike',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));

        // unknown brand

        $item = [
            'brand' => '_reeb_',
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => '[_reeb_]',
            'description' => 'LV',
            'sku' => '1',
        ], $format->apply($item));
    }

    public function test_it_should_do_a_replace_labels_action(): void
    {
        $action = new ReplaceAction();
        $action->setReader($this->getReader());

        $item = [
            'brand' => '_nike_',
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'method' => 'getLabels',
            'locales' => ['nl_BE', 'fr_BE'],
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => [
                'nl_BE' => 'Nike',
                'fr_BE' => 'Nikell'
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));

        $action->setOptions([
            'method' => 'getLabels',
            'locales' => ['nl_BE', 'fr_BE', 'en_US'],
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => [
                'nl_BE' => 'Nike',
                'fr_BE' => 'Nikell',
                'en_US' => null,
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));

        // unknown brand

        $item = [
            'brand' => '_reeb_',
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => [
                'nl_BE' => '[_reeb_]',
                'fr_BE' => '[_reeb_]',
                'en_US' => '[_reeb_]',
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));
    }

    public function test_it_should_do_a_replace_labels_in_list_action(): void
    {
        $action = new ReplaceAction();
        $action->setReader($this->getReader());

        $item = [
            'brand' => ['_nike_','_adi_'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'method' => 'getLabelsFromList',
            'locales' => ['nl_BE', 'fr_BE'],
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => [
                'nl_BE' => ['Nike', 'Adidas'],
                'fr_BE' => ['Nikell', 'Adieu']
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));

        // unknown brand

        $item = [
            'brand' => ['_nike_','_adi_', '_reeb_'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => [
                'nl_BE' => ['Nike', 'Adidas', '[_reeb_]'],
                'fr_BE' => ['Nikell', 'Adieu', '[_reeb_]']
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));

        $item = [
            'brand' => ['_nike_', null, '_adi_', '_reeb_'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => [
                'nl_BE' => ['Nike', null, 'Adidas', '[_reeb_]'],
                'fr_BE' => ['Nikell', null, 'Adieu', '[_reeb_]']
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));
    }

    public function test_it_should_do_a_replace_label_in_list_action(): void
    {
        $action = new ReplaceAction();
        $action->setReader($this->getReader());

        $item = [
            'brand' => ['_nike_','_adi_'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'method' => 'getLabelFromList',
            'locale' => 'nl_BE',
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => ['Nike', 'Adidas'],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));

        // unknown brand

        $item = [
            'brand' => ['_nike_','_adi_', '_reeb_'],
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => ['Nike', 'Adidas', '[_reeb_]'],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));
    }

    public function test_it_should_find_the_label_match_or_null(): void
    {
        $action = new ReplaceAction();
        $action->setReader($this->getReader());

        $item = [
            'brand' => '_newb_',
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'method' => 'getLabels',
            'locales' => ['nl_BE', 'fr_BE', 'en_US'],
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => [
                'nl_BE' => 'new',
                'fr_BE' => null,
                'en_US' => null,
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));
    }

    public function test_it_should_prep_the_labels_when_no_item_is_found(): void
    {
        $action = new ReplaceAction();
        $action->setReader($this->getReader());

        $item = [
            'brand' => [],
            'description' => 'LV',
            'sku' => '1',
        ];

        $action->setOptions([
            'method' => 'getLabelsFromList',
            'locales' => ['nl_BE', 'fr_BE', 'en_US'],
            'key' => 'brand'
        ]);

        $this->assertEquals([
            'brand' => [
                'nl_BE' => [],
                'fr_BE' => [],
                'en_US' => [],
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));
    }

    public function test_it_should_prep_the_reference_from_a_combined_index(): void
    {
        $brands =  [
            [
                'code' => '1',
                'attr' => 'first',
                'label' => [
                    'nl_BE' => 'prime',
                    'fr_BE' => 'Primero',
                ],
            ],
            [
                'code' => '1',
                'attr' => 'brand',
                'label' => [
                    'nl_BE' => 'Nike',
                    'fr_BE' => 'Nikell',
                ],
            ],
            [
                'code' => '2',
                'attr' => 'brand',
                'label' => [
                    'nl_BE' => 'Reebok_nl',
                    'fr_BE' => 'Reebok_fr',
                ],
            ],
        ];

        $repo = new ItemReader(new ItemCollection($brands));

        $action = new ReplaceAction();
        $action->setReader($repo);

        $action->setOptions([
            'method' => 'getLabels',
            'source_filter' => ['attr' => 'brand'],
            'locales' => ['nl_BE', 'fr_BE', 'en_US'],
            'key' => 'brand'
        ]);

        $item = [
            'brand' => '1',
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => [
                'nl_BE' => 'Nike',
                'fr_BE' => 'Nikell',
                'en_US' => null,
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));

        $item = [
            'brand' => '2',
            'description' => 'LV',
            'sku' => '1',
        ];

        $this->assertEquals([
            'brand' => [
                'nl_BE' => 'Reebok_nl',
                'fr_BE' => 'Reebok_fr',
                'en_US' => null,
            ],
            'description' => 'LV',
            'sku' => '1',
        ], $action->apply($item));
    }
}