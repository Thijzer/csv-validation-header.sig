<?php

namespace Misery\Component\Writer;

class JsonWriter extends FileWriter
{
    public function write(array $data): void
    {
        fwrite($this->handle, json_encode($data).PHP_EOL);
    }
}