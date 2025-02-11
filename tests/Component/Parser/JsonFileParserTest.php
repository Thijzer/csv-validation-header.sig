<?php

namespace Tests\Misery\Component\Parser;

use PHPUnit\Framework\TestCase;
use Misery\Component\Parser\JsonFileParser;

class JsonFileParserTest extends TestCase
{
    private string $testFilePath;

    protected function setUp(): void
    {
        // Create a temporary JSON file for testing
        $this->testFilePath = sys_get_temp_dir() . '/test.json';
        file_put_contents($this->testFilePath, json_encode([
            'key1' => 'value1',
            'key2' => 'value2',
            'nested' => [
                'key3' => 'value3',
                'key4' => 'value4'
            ]
        ]));
    }

    protected function tearDown(): void
    {
        // Remove the temporary file after the test
        if (file_exists($this->testFilePath)) {
            unlink($this->testFilePath);
        }
    }

    public function testCreateWithValidFile(): void
    {
        $parser = JsonFileParser::create($this->testFilePath);

        $this->assertInstanceOf(JsonFileParser::class, $parser);
    }

    public function testCreateThrowsExceptionForInvalidFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found:');

        JsonFileParser::create('/invalid/path.json');
    }

    public function testConstructorThrowsExceptionForInvalidJson(): void
    {
        file_put_contents($this->testFilePath, 'Invalid JSON');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to parse JSON:');

        new JsonFileParser(new \SplFileObject($this->testFilePath));
    }

    public function testIteration(): void
    {
        $parser = JsonFileParser::create($this->testFilePath);

        $parser->rewind();
        $this->assertTrue($parser->valid());
        $this->assertEquals('value1', $parser->current());
        $this->assertEquals('key1', $parser->key()); // JSON keys will be strings, not integers.

        $parser->next();
        $this->assertTrue($parser->valid());
        $this->assertEquals('value2', $parser->current());
        $this->assertEquals('key2', $parser->key());


        $parser->next();
        $this->assertTrue($parser->valid());
        $this->assertEquals( [
            'key3' => 'value3',
            'key4' => 'value4'
        ], $parser->current());
        $this->assertEquals('nested', $parser->key());

        $parser->next();
        $this->assertFalse($parser->valid()); // Iterator should be exhausted after last element.
    }

    public function testContainerExtraction(): void
    {
        $parser = JsonFileParser::create($this->testFilePath, 'nested');

        $parser->rewind();
        $this->assertTrue($parser->valid());
        $this->assertEquals('value3', $parser->current());
        $this->assertEquals('key3', $parser->key());

        $parser->next();
        $this->assertTrue($parser->valid());
        $this->assertEquals('value4', $parser->current());
        $this->assertEquals('key4', $parser->key());

        $parser->next();
        $this->assertFalse($parser->valid());
    }
}