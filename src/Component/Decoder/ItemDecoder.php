<?php

namespace Misery\Component\Decoder;

use Misery\Component\Common\Format\ArrayFormat;
use Misery\Component\Common\Format\FlexibleFormat;
use Misery\Component\Common\Format\StringFormat;
use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;

class ItemDecoder
{
    private $configurationRules;

    public function __construct(array $configurationRules)
    {
        $this->configurationRules = $configurationRules;
    }

    public function decode(array $item): array
    {
        foreach ($this->configurationRules['property'] ?? [] as $property => $matches) {
            if (isset($item[$property])) {
                foreach ($matches as $match) {
                    $this->processMatch($item, $property, $match);
                }
            }
        }

        foreach ($this->configurationRules['item'] ?? [] as $property => $matches) {
            foreach ($matches as $match) {
                $this->processMatch($item, $property, $match);
            }
        }

        return $item;
    }

    private function processMatch(array &$item, string $property, array $match): void
    {
        $class = $match['class'] ?? null;

        if ($class instanceof OptionsInterface && !empty($match['options'])) {
            $class->setOptions($match['options']);
        }

        switch (true) {
            case $class instanceof FlexibleFormat:
            case $class instanceof StringFormat:
                $item[$property] = $class->reverseFormat($item[$property]);
                break;
            case $class instanceof ArrayFormat:
                $item = $class->reverseFormat($item);
                break;
            case $class instanceof RowModifier:
                $item = $class->reverseModify($item);
                break;
        }
    }
}