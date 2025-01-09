<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\StripTagsModifier;
use Misery\Component\Modifier\UrlEncodeModifier;
use PHPUnit\Framework\TestCase;

class StripTagsModifierTest extends TestCase
{
    function test_it_should_strip_html_tags()
    {
        $modifier = new StripTagsModifier();

        // Test case 1: Value without HTML tags
        $valueWithoutTags = 'This is a plain text.';
        $modifiedValue = $modifier->modify($valueWithoutTags);
        $this->assertEquals($valueWithoutTags, $modifiedValue);

        // Test case 2: Value with HTML tags
        $valueWithTags = '<p>This is <b>formatted</b> text.</p>';
        $expectedModifiedValue = 'This is formatted text.';
        $modifiedValue = $modifier->modify($valueWithTags);
        $this->assertEquals($expectedModifiedValue, $modifiedValue);

        // Test case 3: Value with malicious HTML tags
        $valueWithMaliciousTags = '<script>alert("Hello!");</script>';
        $expectedModifiedValue = 'alert("Hello!");';
        $modifiedValue = $modifier->modify($valueWithMaliciousTags);
        $this->assertEquals($expectedModifiedValue, $modifiedValue);
    }
}