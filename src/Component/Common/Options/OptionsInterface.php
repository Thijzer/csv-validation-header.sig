<?php

namespace Misery\Component\Common\Options;

interface OptionsInterface
{
    public function setOptions(array $options = []): void;
    /**
     * @param string $key
     * @return mixed
     */
    public function getOption(string $key);
    public function getOptions(): array;
}