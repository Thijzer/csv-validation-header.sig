<?php

namespace Tests\Misery\Component\Action;
use Misery\Component\Action\KeyMapperAction;
use Misery\Component\Converter\Matcher;
use PHPUnit\Framework\TestCase;

class KeyMapperActionTest extends TestCase
{
    public function testApplyWithListMapping()
    {
        $item = ['key1' => 'value1', 'key2' => 'value2'];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['key1' => 'new_key1', 'key2' => 'new_key2']
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['new_key1' => 'value1', 'new_key2' => 'value2'], $result);
    }

    public function testApplyWithMatcherMapping()
    {
        $item = [
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'values|key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
            'key3' => 'value3',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['key2' => 'new_key2']
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'new_key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
            'key3' => 'value3',
        ], $result);
    }

    public function testApplyWithMatcherMappingInReverse()
    {
        $item = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['new_key2' => 'key2'],
            'reverse' => true,
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'key1' => 'value1',
            'new_key2' => 'value2',
            'key3' => 'value3',
        ], $result);
    }

    public function testApplyWithMatcherMappingInReverseWithMatcher()
    {
        $item = [
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'values|key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
            'key3' => 'value3',
        ];

        $action = new KeyMapperAction();
        $action->setOptions([
            'list' => ['values|new_key2' => 'values|key2'],
            'reverse' => true,
        ]);

        $result = $action->apply($item);

        $this->assertEquals([
            'values|key1' => [
                'matcher' => Matcher::create('key1'),
                'data' => 'value1',
            ],
            'key3' => 'value3',
            'values|new_key2' => [
                'matcher' => Matcher::create('key2'),
                'data' => 'value2',
            ],
        ], $result);
    }
}
