<?php

namespace Tests\Misery\Component\Format;

use Misery\Component\Format\StringToListFormat;
use PHPUnit\Framework\TestCase;

class ListFormatTest extends TestCase
{
    public function test_it_should_list_type_a_value(): void
    {
        $format = new StringToListFormat();

        $format->setOptions([
            'separator' => ',',
        ]);

        $this->assertSame($format->format('a,b,c'), ['a', 'b', 'c']);
    }

    public function test_it_should_reverse_list_type_a_value(): void
    {
        $format = new StringToListFormat();

        $format->setOptions([
            'separator' => ',',
        ]);

        $this->assertSame($format->reverseFormat(['a', 'b', 'c']), 'a,b,c');

        $localizedFormat = [
            'nl_BE' => ['a', 'b', 'c'],
            'fr_BE' => ['d', 'e', 'f'],
        ];

        $this->assertSame($format->reverseFormat($localizedFormat), [
            'nl_BE' => 'a,b,c',
            'fr_BE' => 'd,e,f',
        ]);

        $localizedFormat = [
            'pim' => [
                'nl_BE' => ['a', 'b', 'c'],
                'fr_BE' => ['d', 'e', 'f'],
            ],
            'pam' => [
                'nl_BE' => ['a1', 'b2', 'c3'],
                'fr_BE' => ['d4', 'e5', 'f6'],
            ],
        ];

        $this->assertSame($format->reverseFormat($localizedFormat), [
            'pim' => [
                'nl_BE' => 'a,b,c',
                'fr_BE' => 'd,e,f',
            ],
            'pam' => [
                'nl_BE' => 'a1,b2,c3',
                'fr_BE' => 'd4,e5,f6',
            ],
        ]);

        $localizedFormat = [
            'nl_BE' => ['a', 'b', 'c'],
            'fr_BE' => [],
        ];
        $this->assertSame($format->reverseFormat($localizedFormat), [
            'nl_BE' => 'a,b,c',
            'fr_BE' => null,
        ]);
    }
}