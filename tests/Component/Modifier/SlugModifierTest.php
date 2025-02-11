<?php

namespace Tests\Misery\Component\Modifier;

use Misery\Component\Modifier\SlugModifier;
use PHPUnit\Framework\TestCase;

class SlugModifierTest extends TestCase
{
    private SlugModifier $slugModifier;

    protected function setUp(): void
    {
        $this->slugModifier = new SlugModifier();
    }

    public function slugifyDataProvider(): array
    {
        return [
            // Basic test cases
            ['Hello World!', 'hello-world'],
            ['PHP is great', 'php-is-great'],

            // European languages test cases
            ['Éléphant', 'elephant'],
            ['über', 'uber'],
            ['smörgåsbord', 'smorgasbord'],
            ['façade', 'facade'],
            ['mañana', 'manana'],
            ['crème brûlée', 'creme-brulee'],
            ['München', 'munchen'],
            ['Český', 'cesky'],
            ['Zażółć gęślą jaźń', 'zazolc-gesla-jazn'],
            ['Dvořák', 'dvorak'],
            ['BAHAMA van Ravøtt voor Heren', 'bahama-van-ravott-voor-heren'],

            // Additional Cyrillic characters
            ['єдиноріг', 'edinorig'],
            ['історія', 'istoriya'],
            ['їжа', 'izha'],
            ['ґрунт', 'grunt'],
            ['Європа', 'evropa'],
            ['Іван', 'ivan'],
            ['Їжак', 'izhak'],
            ['Ґалаґан', 'galagan'],

            // Special characters
            ['Café!', 'cafe'],
            ['naïve', 'naive'],
            ['coöperate', 'cooperate'],

            // Edge cases
            ['----Hello----World----', 'hello-world'],
            ['multiple   spaces', 'multiple-spaces'],
            ['!!!---symbols---!!!', 'symbols'],
            ['', ''], // Empty string
            ['-already-slugified-', 'already-slugified'],
        ];
    }

    /**
     * @dataProvider slugifyDataProvider
     */
    public function testModify($input, $expected)
    {
        $this->assertEquals($expected, $this->slugModifier->modify($input));
    }
}
