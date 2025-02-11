<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\DecodeSpecialModifier;
use PHPUnit\Framework\TestCase;

class DecodeSpecialCharTest extends TestCase
{
    function test_it_should_decode_all_chars_and_quotes(): void
    {
        $modifier = new DecodeSpecialModifier();
        $modifier->setOptions(['decoder' => "quotes"]);

        $this->assertEquals('Test&Test', $modifier->modify('Test&amp;Test'));
        $this->assertEquals('§èé&çà', $modifier->modify('&sect;&egrave;&eacute;&amp;&ccedil;&agrave;'));

        $this->assertEquals('Albert Einstein said: \'E=MC²\'',
            $modifier->modify('Albert Einstein said: &#039;E=MC&sup2;&#039;'));

        $this->assertEquals('My name is Øyvind Åsane. I\'m Norwegian.',
            $modifier->modify('My name is &Oslash;yvind &Aring;sane. I&#039;m Norwegian.'));

        $this->assertEquals('I\'ll "walk" the <b>dog</b> now',
            $modifier->modify('I\'ll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now'));
    }

    function test_it_should_decode_all_chars_and_double_quotes(): void
    {
        $modifier = new DecodeSpecialModifier();
        $modifier->setOptions(['decoder' => "double quotes"]);

        $this->assertEquals('My name is Øyvind Åsane. I&#039;m Norwegian.',
            $modifier->modify('My name is &Oslash;yvind &Aring;sane. I&#039;m Norwegian.'));

        $this->assertEquals('I\'ll "walk" the <b>dog</b> now',
            $modifier->modify('I\'ll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now'));
    }

    function test_it_should_decode_all_chars_and_no_quotes(): void
    {
        $modifier = new DecodeSpecialModifier();
        $modifier->setOptions(['decoder' => "no quotes"]);

        $this->assertEquals('My name is Øyvind Åsane. I&#039;m Norwegian.',
            $modifier->modify('My name is &Oslash;yvind &Aring;sane. I&#039;m Norwegian.'));

        $this->assertEquals('I\'ll &quot;walk&quot; the <b>dog</b> now',
            $modifier->modify('I\'ll &quot;walk&quot; the &lt;b&gt;dog&lt;/b&gt; now'));
    }

    function test_it_should_decode_xml_chars(): void
    {
        $modifier = new DecodeSpecialModifier();
        $modifier->setOptions(['decoder' => "xml 1"]);

        $this->assertEquals('The currency of europe is €',
            $modifier->modify('The currency of europe is &#8364;'));

        $this->assertEquals('tom&jerry',
            $modifier->modify('tom&#38;jerry'));

        $this->assertNotEquals('_',
            $modifier->modify('&lowbar;'));
    }

    function test_it_should_decode_xhtml_chars(): void
    {
        $modifier = new DecodeSpecialModifier();
        $modifier->setOptions(['decoder' => "xhtml"]);

        $this->assertEquals('tom&jerry',
            $modifier->modify('tom&#38;jerry'));

        $this->assertEquals('The currency of europe is €',
            $modifier->modify('The currency of europe is &#8364;'));

        $this->assertNotEquals('_',
            $modifier->modify('&lowbar;'));
    }

    function test_it_should_decode_html5_chars(): void
    {
        $modifier = new DecodeSpecialModifier();
        $modifier->setOptions(['decoder' => "html 5"]);

        $this->assertEquals('I will walk this cat when i want to!',
            $modifier->modify('I will walk this cat when i want to&excl;'));

        $this->assertEquals('the currency of america is $',
            $modifier->modify('the currency of america is &dollar;'));

        $this->assertEquals('_',
            $modifier->modify('&lowbar;'));
    }
}