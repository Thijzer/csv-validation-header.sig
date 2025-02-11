<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\BindAction;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Source\Command\SourceFilterCommand;
use Misery\Component\Source\Source;
use Misery\Component\Source\SourceFilter;
use PHPUnit\Framework\TestCase;

class BindActionTest extends TestCase
{
    public function testApplyWithValues()
    {
        $item = [
            'identifier' => '1234',
            'color' => 'red',
        ];
        $colorSet = new ItemCollection([
            $colorItem = ['code' => 'red', 'type' => 'select', 'labels' => ['nl_BE' => 'rood', 'fr_BE' => 'rouge']],
            ['code' => 'green', 'type' => 'select', 'labels' => ['nl_BE' => 'groen', 'fr_BE' => 'ver']],
        ]);

        $cmd = new SourceFilterCommand();
        $cmd->setSource(Source::createSimple($colorSet, 'colors'));
        $cmd->setOptions([
            'filter' => [
                'code' => '$color',
            ],
        ]);

        $sourceFilter = new SourceFilter($cmd);

        $action = new BindAction();
        $action->setOptions([
            'list' => ['color'],
            'filter' => $sourceFilter,
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'identifier' => '1234',
            'color' => $colorItem,
        ], $result);
    }

    public function testApplyWithValuesWithMustaches()
    {
        $item = [
            'identifier' => '1234',
            'color' => 'red',
        ];
        $colorSet = new ItemCollection([
            $colorItem = ['code' => 'red', 'type' => 'select', 'labels' => ['nl_BE' => 'rood', 'fr_BE' => 'rouge']],
            ['code' => 'green', 'type' => 'select', 'labels' => ['nl_BE' => 'groen', 'fr_BE' => 'ver']],
        ]);

        $cmd = new SourceFilterCommand();
        $cmd->setSource(Source::createSimple($colorSet, 'colors'));
        $cmd->setOptions([
            'filter' => [
                'code' => '{color}',
            ],
        ]);

        $sourceFilter = new SourceFilter($cmd);

        $action = new BindAction();
        $action->setOptions([
            'field' => 'color',
            'filter' => $sourceFilter,
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'identifier' => '1234',
            'color' => $colorItem,
        ], $result);
    }
}
