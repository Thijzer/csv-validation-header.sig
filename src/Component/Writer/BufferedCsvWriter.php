<?php

namespace Misery\Component\Writer;

use Assert\Assert;
use Misery\Component\Parser\JsonParser;

/**
 * This CSV writer, writes all items to a JSON buffer first,
 * during this process it will collect the headers
 * When the writer is closed it will write all buffered items into CSV
 * - null values are kept
 */
class BufferedCsvWriter implements ItemWriterInterface
{
    private JsonWriter $jsonWriter;
    private CsvWriter $csvWriter;

    private array $headers = [];
    private string|false $tmpFile;

    public function __construct(
        array $setup
    ) {
        $this->jsonWriter = new JsonWriter($this->tmpFile = tempnam(sys_get_temp_dir(), 'SmartCSV'));
        $this->csvWriter = CsvWriter::createFromArray($setup);
    }

    public static function createFromArray(array $configuration): BufferedCsvWriter
    {
        Assert::that($configuration)->keyIsset('filename');
        Assert::that($configuration['filename'])->notEmpty();

        return new self($configuration);
    }

    public function write(array $data): void
    {
        $this->setHeader(array_fill_keys(array_keys($data), null));
        $this->jsonWriter->write($data);
    }

    public function close(): void
    {
        $reader = JsonParser::create($this->tmpFile);

        $this->csvWriter->setHeader(array_keys($this->headers));
        while ($item = $reader->current()) {
            $this->csvWriter->write(array_replace($this->headers, $item));
            $reader->next();
        }
        $this->csvWriter->close();
        unlink($this->tmpFile);
    }

    public function setHeader(array $headers): void
    {
        $this->headers = array_replace($this->headers, $headers);
    }
}