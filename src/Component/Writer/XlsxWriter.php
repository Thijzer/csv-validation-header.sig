<?php

namespace Misery\Component\Writer;

class XlsxWriter implements ItemWriterInterface
{
    private $filename;
    private $writer;
    private $sheetName = 'MySheet1';
    private $header = null;

    public function __construct(
        array $options = []
    ) {
        $this->filename = $options['filename'];
        $this->writer = new Xlsx();
        $this->writer->setAuthor('Induxx');
    }

    public function write(array $data): void
    {
        if (!$this->header) {
            $this->header = array_keys($data);
            $typeArray = array_fill(0, count($this->header), 'string');
            $this->header = array_combine($this->header, $typeArray);
            $this->writer->writeSheetHeader($this->sheetName, $this->header);
        }

        $this->writer->writeSheetRow($this->sheetName, array_values($data));
    }

    public function close(): void
    {
        $this->writer->writeToFile($this->filename);
    }
}
