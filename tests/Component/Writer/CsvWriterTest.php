<?php

namespace Tests\Misery\Component\Writer;

use Misery\Component\Parser\CsvParser;
use Misery\Component\Writer\CsvWriter;
use PHPUnit\Framework\TestCase;

class CsvWriterTest extends TestCase
{
    private $items = [
        [
            'id' => '1',
            'first_name' => 'Gordie',
        ],
        [
            'id' => "2",
            'first_name' => 'Frans',
        ],
    ];

    public function test_parse_csv_file(): void
    {
        $filename = __DIR__ . '/../../examples/STD_OUT';
        $writer = new CsvWriter($filename);

        foreach ($this->items as $item) {
            $writer->write($item);
        }

        $file = new \SplFileObject($filename);

        $this->assertTrue($file->isFile());

        $parser = CsvParser::create($filename);

        $this->assertSame(2, $parser->count());

        $this->assertSame($this->items[0], $parser->current());

        $writer->clear();
    }
}