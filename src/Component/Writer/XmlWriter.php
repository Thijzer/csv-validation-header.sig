<?php

namespace Misery\Component\Writer;

use Assert\Assertion;

class XmlWriter implements ItemWriterInterface
{
    public const CONTAINER = 'container';
    public const HEADER = 'header';
    public const START = 'start';

    private $options;
    /** @var array */
    private $header = [
        'version' => '1.0',
        'encoding' => 'UTF-8',
        'indent' => 4,
        'indent_string' => ' ',
    ];

    /**  @var \XMLWriter */
    private $writer;
    /** @var string */
    private $filename;

    public function __construct(
        string $filename,
        array $options = []
    ) {
        $this->filename = $filename;
        Assertion::writeable($filename);

        $this->options = $options;
        $start = isset($options[self::START]) > 0 ? $options[self::START]: [];
        $header = array_merge($this->header, $options[self::HEADER] ?? []);

        $XMLWriter = new \XMLWriter();
        $XMLWriter->openMemory();
        $XMLWriter->openUri($filename);
        if (isset($header['version'], $header['encoding'])) {
            $XMLWriter->startDocument($header['version'], $header['encoding']);
        }
        $XMLWriter->setIndent($header['indent']);
        $XMLWriter->setIndentString(str_repeat($header['indent_string'], $header['indent']));

        $this->writer = $XMLWriter;
        // write the start
        foreach ($start as $elemName => $elemAttributes) {
            $XMLWriter->startElement($elemName);
            foreach ($elemAttributes['@attributes'] ?? [] as $attributeName => $attributeValue) {
                $XMLWriter->writeAttribute($attributeName, $attributeValue);
            }
        }

        // open the container
        foreach ($options as $elemName => $elemAttributes) {
            if ($elemName === self::CONTAINER) {
                $this->writer->startElement($elemAttributes);
            }
        }
    }

    public function write(array $data): void
    {
        if (isset($data['@attributes'])) {
            foreach ($data['@attributes'] as $attributeName => $attributeValue) {
                $this->writer->writeAttribute($attributeName, $attributeValue);
            }
            unset($data['@attributes']);
        }
        if (isset($data['@data'])) {
            $this->writer->text($data['@data']);
            return;
        }
        if (isset($data['@CDATA'])) {
            $this->writer->writeCdata($data['@CDATA']);
            return;
        }
        foreach($data as $key => $value) {
            if (\is_array($value)) {
                if (\is_string($key) && is_numeric(current(array_keys($value)))) {
                    foreach ($value as $i => $collectionValue) {
                        $this->writer->startElement($key);
                        $this->write($collectionValue);
                        $this->writer->endElement();
                    }
                    continue;
                }

                if (\is_string($key)) {
                    $this->writer->startElement($key);
                    $this->write($value);
                    $this->writer->endElement();
                    continue;
                }

                if (\is_numeric($key)) {
                    $this->write($value);
                }

                continue;
            }

            if (is_string($key) && is_string($value) && !empty($key)) {
                $this->writer->writeElement($key, $value);
            }
        }
    }

    public function clear(): void
    {
        file_put_contents($this->filename, '');
    }

    public function close(): void
    {
        $writer = $this->writer;

        // close the container
        if (isset($this->options[self::CONTAINER])) {
            $writer->endElement();
        }

        // close the start
        if (isset($this->options[self::START])) {
            $writer->endElement();
        }

        $writer->endDocument();

        $writer->flush();
    }

    public function __destruct()
    {
        $this->close();
    }
}