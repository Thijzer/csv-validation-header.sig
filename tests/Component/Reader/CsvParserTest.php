<?php

namespace Tests\Misery\Component\Reader;

use Misery\Component\Parser\CsvParser;
use Misery\Component\Parser\Exception\InvalidCsvElementSizeException;
use PHPUnit\Framework\TestCase;

class CsvParserTest extends TestCase
{
    public function test_parse_csv_file(): void
    {
        $file = new \SplFileObject(__DIR__ . '/../../examples/users.csv');
        $reader = new CsvParser($file, ';');

        $this->assertTrue($file->isFile());
        $this->assertSame($reader->count(), 300);

        $count = 0;
        while ($reader->current()) {
            $count++;
            $reader->next();
        }

        $this->assertSame($count, 300);
    }

    public function test_parse_csv_with_iterator(): void
    {
        $file = new \SplFileObject(__DIR__ . '/../../examples/users.csv');
        $reader = new CsvParser($file, ';');

        $this->assertTrue($file->isFile());
        $this->assertSame($reader->count(), 300);

        $count = 0;
        foreach ($reader->getIterator() as $item) {
            $count++;
        }

        $this->assertSame($count, 300);
    }

    public function test_parse_csv_with_loop(): void
    {
        $file = new \SplFileObject(__DIR__ . '/../../examples/users.csv');
        $reader = new CsvParser($file, ';');

        $this->assertTrue($file->isFile());
        $this->assertSame($reader->count(), 300);

        $count = 0;
        $reader->loop(function ($row) use (&$count) {
            $count++;
        });

        $this->assertSame($count, 300);
    }

    public function test_parse_csv_with_exception_with_larger_header(): void
    {
        $this->expectExceptionMessage('Invalid CSV Element size on file(corrupt_users_extra.csv) : lineNumber(1) : headers({"10":"company"}): item({"10":null})');

        $file = new \SplFileObject(__DIR__ . '/../../examples/corrupt_users_extra.csv');
        $parser = new CsvParser($file, ';');

        $parser->loop(function ($row) use (&$count) {});
    }

    public function test_parse_csv_with_exception_with_shorter_header(): void
    {
        $this->expectExceptionMessage('Invalid CSV Element size on file(corrupt_users.csv) : lineNumber(1) : headers({"9":null}): item({"9":"+32 456 273 2460"})');

        $file = new \SplFileObject(__DIR__ . '/../../examples/corrupt_users.csv');
        $parser = new CsvParser($file, ';');

        $parser->loop(function ($row) use (&$count) {});
    }
}