<?php

namespace Misery\Component\Writer;

use Symfony\Component\Yaml\Yaml;

class YamlWriter extends FileWriter
{
    private $items = [];

    public function write(array $data): void
    {
        $this->items[] = $data;
    }

    public function close(): void
    {
        file_put_contents($this->getFilename(), Yaml::dump($this->items));
        parent::close();
    }
}