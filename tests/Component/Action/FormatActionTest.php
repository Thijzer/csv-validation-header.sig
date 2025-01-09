<?php

namespace Tests\Misery\Component\Action;

use Misery\Component\Action\FormatAction;
use PHPUnit\Framework\TestCase;

class FormatActionTest extends TestCase
{
    public function testApplyReplaceFunction()
    {
        $item = ['field' => 'abc123'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['replace'],
            'search' => '123',
            'replace' => '456'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'abc456'], $result);
    }

    public function testApplyNumberFunction()
    {
        $item = ['field' => 12345.6789];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['number'],
            'decimals' => 2,
            'decimal_sep' => ',',
            'mille_sep' => '.'
        ]);

        $result = $action->apply($item);
        $this->assertEquals(['field' => '12.345,68'], $result);

        $item = ['field' => '12345.6789'];

        $result = $action->apply($item);
        $this->assertEquals(['field' => '12.345,68'], $result);

        $action->setOptions([
            'decimals' => 4,
            'decimal_sep' => ',',
            'mille_sep' => ''
        ]);

        $result = $action->apply($item);
        $this->assertEquals(['field' => '12345,6789'], $result);
    }

    public function testApplyEmptyNumberFunction()
    {
        $item = ['field' => ''];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['number'],
            'decimals' => 2,
            'decimal_sep' => ',',
            'mille_sep' => '.'
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => ''], $result);
    }

    public function testEmptyReplaceFormatFunction()
    {
        $item = ['field' => 'A|,B|,C|,D'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['replace'],
            'search' => '|,',
            'replace' => '',
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'ABCD'], $result);

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['replace'],
            'search' => '',
            'replace' => null,
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'A|,B|,C|,D'], $result);
    }

    public function testApplyPrefixFunction()
    {
        $item = ['field' => '12345'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['prefix'],
            'prefix' => 'test|',
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'test|12345'], $result);
    }

    public function testApplySuffixFunction()
    {
        $item = ['field' => '12345'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['suffix'],
            'suffix' => '|test',
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => '12345|test'], $result);
    }

    public function testApplySprintfFunction()
    {
        $item = ['field' => '12345'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['sprintf'],
            'format' => '%s|test',
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => '12345|test'], $result);
    }

    public function testApplyExplodeFunction()
    {
        $item = ['field' => '1,2,3,4,5'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['explode'],
            'separator' => ',',
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => [1,2,3,4,5]], $result);
    }

    public function testApplySelectIndexFunction()
    {
        $item = ['field' => 'A,B'];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['explode', 'select_index'],
            'index' => 0,
            'separator' => ',',
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'A'], $result);
    }

    public function testApplySelectIndexNoMultiValFunction()
    {
        $item = ['field' => ['A']];

        $action = new FormatAction();
        $action->setOptions([
            'field' => 'field',
            'functions' => ['select_index'],
            'index' => 0,
            'multi_value' => false,
        ]);

        $result = $action->apply($item);

        $this->assertEquals(['field' => 'A'], $result);
    }
}
