<?php

namespace Component\Statement;

use Misery\Component\Statement\ItemPlaceholder;
use PHPUnit\Framework\TestCase;

class ItemPlaceholderTest extends TestCase
{
    public function testExtract()
    {
        $this->assertEquals('value', ItemPlaceholder::extract('{value}'), 'Extracts the value inside the braces.');
        $this->assertEquals('item', ItemPlaceholder::extract('{item}'), 'Extracts the value inside the braces.');
        $this->assertEquals('item', ItemPlaceholder::extract('item'), 'Returns the same value if no braces are present.');
    }

    public function testMatches()
    {
        $this->assertTrue(ItemPlaceholder::matches('{value}'), 'Returns true if the value does not match after extraction.');
        $this->assertFalse(ItemPlaceholder::matches('value'), 'Returns false if the value matches after extraction.');
        $this->assertFalse(ItemPlaceholder::matches('va}{}{ue'), 'Returns false if the value matches after extraction.');
        $this->assertFalse(ItemPlaceholder::matches('va}{}{ue'), 'Returns false if the value matches after extraction.');
        $this->assertFalse(ItemPlaceholder::matches('{value'), 'Returns false if the value matches after extraction.');
        $this->assertFalse(ItemPlaceholder::matches('value}'), 'Returns false if the value matches after extraction.');
    }

    public function testTextReplace()
    {
        $text = 'Hello, {name}!';
        $item = ['name' => 'John'];
        $this->assertEquals('Hello, John!', ItemPlaceholder::textReplace($text, $item), 'Replaces placeholder with value from array.');

        $textWithNoReplacement = 'Hello, {user}!';
        $this->assertEquals('Hello, {user}!', ItemPlaceholder::textReplace($textWithNoReplacement, $item), 'Leaves placeholder if no matching key in array.');

        $emptyText = '';
        $this->assertEquals('', ItemPlaceholder::textReplace($emptyText, $item), 'Handles empty string correctly.');

        $textWithMultiplePlaceholders = '{greeting}, {name}!';
        $itemMulti = ['greeting' => 'Hello', 'name' => 'John'];
        $this->assertEquals('Hello, John!', ItemPlaceholder::textReplace($textWithMultiplePlaceholders, $itemMulti), 'Correctly replaces multiple placeholders.');
    }
}