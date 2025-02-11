<?php

namespace Tests\Misery\Component\Writer;

use Misery\Component\Parser\JsonParser;
use Misery\Component\Writer\JsonWriter;
use PHPUnit\Framework\TestCase;

class JsonWriterTest extends TestCase
{
    private $items = [
        [
            'id' => 1,
            'first_name' => 'Gordie',
            'active' => true,
            'description' => [
                [
                    'locale' => 'nl',
                    'description' => 'Gordie-desc-nl',
                ],
                [
                    'locale' => 'fr',
                    'description' => 'Gordie-desc-fr',
                ],
            ],
        ],
        [
            'id' => 2,
            'first_name' => 'Frans',
            'active' => true,
            'description' => [
                [
                    'locale' => 'nl',
                    'description' => 'Frans-desc-nl',
                ],
                [
                    'locale' => 'fr',
                    'description' => 'Frans-desc-fr',
                ],
            ],
        ],
    ];

    public function test_parse_json_file(): void
    {
        $filePath = __DIR__ . '/../../examples/new_users.json';
        $writer = new JsonWriter($filePath);

        foreach ($this->items as $item) {
            $writer->write($item);
        }

        $file = new \SplFileObject($filePath);

        self::assertTrue($file->isFile());

        $parser = JsonParser::create($filePath);

        $result = [];
        while ($item = $parser->current()) {
            $result[] = $item;
            $parser->next();
        }

        self::assertSame($this->items, $result);

        unlink($filePath);
    }
}