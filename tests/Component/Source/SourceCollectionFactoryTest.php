<?php

namespace Tests\Misery\Component\Source;

use Misery\Component\Source\SourceCollectionFactory;
use Misery\Component\Source\SourceCollection;
use Misery\Component\Parser\JsonFileParser;
use Misery\Component\Parser\JsonParser;
use PHPUnit\Framework\TestCase;

class SourceCollectionFactoryTest extends TestCase
{
    private string $jsonlFile;
    private string $jsonFile;

    protected function setUp(): void
    {
        // Create a JSONL test file
        $this->jsonlFile = tempnam(sys_get_temp_dir(), 'jsonl'). '.json';
        file_put_contents($this->jsonlFile, '{"id":1,"name":"Item1"}' . PHP_EOL . '{"id":2,"name":"Item2"}');

        // Create a regular JSON test file
        $this->jsonFile = tempnam(sys_get_temp_dir(), 'json'). '.json';
        file_put_contents($this->jsonFile, json_encode([
            ["id" => 1, "name" => "Item1"],
            ["id" => 2, "name" => "Item2"]
        ]));
    }

    protected function tearDown(): void
    {
        // Clean up temporary files
        @unlink($this->jsonlFile);
        @unlink($this->jsonFile);
    }

    public function testJsonlFileIsParsedWithJsonParser(): void
    {
        $sourceCollection = new SourceCollection('test');
        $factory = new SourceCollectionFactory();

        $factory->createFromConfiguration([$this->jsonlFile], $sourceCollection);

        $source = $sourceCollection->get(basename($this->jsonlFile));
        $this->assertInstanceOf(JsonParser::class, $source->getCursor());
    }

    public function testJsonFileIsParsedWithJsonFileParser(): void
    {
        $sourceCollection = new SourceCollection('test');
        $factory = new SourceCollectionFactory();

        $factory->createFromConfiguration([$this->jsonFile], $sourceCollection);

        $source = $sourceCollection->get(basename($this->jsonFile));
        $this->assertInstanceOf(JsonFileParser::class, $source->getCursor());
    }
}
