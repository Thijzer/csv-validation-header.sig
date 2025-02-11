<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\CopyAction;
use Misery\Component\Action\ItemActionProcessorFactory;
use Misery\Component\Action\RemoveAction;
use Misery\Component\Action\RenameAction;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Source\SourceCollection;
use PHPUnit\Framework\TestCase;

class ActionProcessorTest extends TestCase
{
    private $item = [
        'id' => '1',
        'supplier' => 'Gordie',
        'remove_me' => 'AK',
    ];

    public function test_encode_item(): void
    {
        $registry = new Registry('action');
        $registry
            ->register(CopyAction::NAME, new CopyAction())
            ->register(RenameAction::NAME, new RenameAction())
            ->register(RemoveAction::NAME, new RemoveAction())
        ;

        $actionFactory = new ItemActionProcessorFactory($registry);

        $sources = new SourceCollection('akeneo/csv');

        $decoder = $actionFactory->createActionProcessor($sources, [
            'rename_supplier' => [
                'action' => 'rename',
                'from' => 'supplier',
                'to' => 'test01',
            ],
            'copy_supplier' => [
                'action' => 'copy',
                'from' => 'test01',
                'to' => 'test02',
            ],
            'remove_me' => [
                'action' => 'remove',
                'keys' => ['remove_me'],
            ],
        ]);

        $decoder2 = $actionFactory->createActionProcessor($sources, [
            'rename_supplier' => [
                'action' => 'rename',
                'from' => 'supplier',
                'to' => 'test01',
            ],
            'copy_supplier' => [
                'action' => 'copy',
                'from' => 'test01',
                'to' => 'test02',
            ],
            'copy_supplier_2' => [
                'action' => 'copy',
                'from' => 'test02',
                'to' => 'test03',
            ],
        ]);

        $this->assertSame($decoder->process($this->item), [
            'id' => '1',
            'test01' => 'Gordie',
            'test02' => 'Gordie',
        ]);

        $this->assertSame($decoder2->process($this->item), [
            'id' => '1',
            'test01' => 'Gordie',
            'remove_me' => 'AK',
            'test02' => 'Gordie',
            'test03' => 'Gordie',
        ]);
    }
}