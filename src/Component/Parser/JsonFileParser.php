<?php

namespace Misery\Component\Parser;

class JsonFileParser extends FileParser
{
    public static function create(string $filename): self
    {
        return new self(new \SplFileObject($filename));
    }

    /**
     * @return false|array
     */
    public function current(): mixed
    {
        if ($item = parent::current()) {
            $item = \json_decode(str_replace(PHP_EOL, '', $item), true);
            if (!is_array($item)) {
                throw new \RuntimeException(
                    sprintf(
                        'Unable to parse file %s on line %s : message : %s',
                        $this->file->getRealPath(),
                        $this->file->key(),
                        json_last_error()
                ));
            }
            return $item;
        }

        return false;
    }
}