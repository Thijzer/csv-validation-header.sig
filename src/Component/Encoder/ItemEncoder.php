<?php

namespace Misery\Component\Encoder;

use Misery\Component\Common\Format\ArrayFormat;
use Misery\Component\Common\Format\FlexibleFormat;
use Misery\Component\Common\Format\StringFormat;
use Misery\Component\Common\Modifier\CellModifier;
use Misery\Component\Common\Modifier\RowModifier;
use Misery\Component\Common\Options\OptionsInterface;

class ItemEncoder
{
    private array $configurationRules = [
        'item' => [],
        'property' => [],
    ];

    public function __construct(array $configurationRules)
    {
        $this->configurationRules['item'] = $configurationRules['item'] ?? [];
        $this->configurationRules['property'] = $configurationRules['property'] ?? [];
    }

    public function encode(array $item): array
    {
        foreach ($this->configurationRules['property'] as $property => $matches) {
            if (array_key_exists($property, $item)) {
                foreach ($matches as $match) {
                    $this->processMatch($item, $property, $match);
                }
                continue;
            }
            foreach ($matches as $match) {
                $this->processArrayMatch($item, $match, $property);
            }
        }

        // process item modifiers last, as they can modify the whole item
        foreach ($this->configurationRules['item'] as $match) {
            $this->processArrayMatch($item, $match);
        }

        return $item;
    }

    private function processArrayMatch(array &$item, array $match, string $property = null): void
    {
        $class = $match['class'] ?? null;

        if ($class instanceof OptionsInterface && !empty($match['options'])) {
            $class->setOptions($match['options']);
        }

        switch (true) {
            case $class instanceof ArrayFormat:
                $class->setOptions(['field' => $property]);
                $item = $class->format($item);
                break;
            case $class instanceof RowModifier:
                $item = $class->modify($item);
                break;
        }
    }

    private function processMatch(array &$item, string $property, array $match): void
    {
        $class = $match['class'] ?? null;

        if ($class instanceof OptionsInterface && !empty($match['options'])) {
            $class->setOptions($match['options']);
        }

        switch (true) {
            case $class instanceof CellModifier:
                $item[$property] = $class->modify($item[$property]);
                break;
            case $class instanceof FlexibleFormat:
            case $class instanceof StringFormat:
                $item[$property] = $class->format($item[$property]);
                break;
        }
    }
}