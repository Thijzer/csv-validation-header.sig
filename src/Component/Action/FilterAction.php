<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Converter\Matcher;

class FilterAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'filter';

    /** @var array */
    private $options = [
        'match' => null,
        'equals' => null,
        'key' => null,
        'field' => null,
        'case-sensitive' => false,
        'not-starts-with' => null,
        'starts-with' => null,
    ];

    public function apply($item)
    {
        $field = $this->getOption('key', $this->getOption('field')); # legacy support
        $listItem = $item[$field] ?? null;
        if (null === $listItem || null === $field) {
            return $item;
        }

        if ($this->getOption('match')) {
            if (is_array($listItem)) {
                $item[$field] = array_filter($listItem, function ($itemValue) {
                    return $this->hasMatch($itemValue) !== true;
                });
            }

            if (is_string($listItem) && $this->hasMatch($listItem)) {
                $item[$field] = null;
                return $item;
            }
        }

        if ($this->getOption('equals')) {
            if (is_array($listItem)) {
                $item[$field] = array_filter($listItem, function ($itemValue) {
                    return $this->equals($itemValue) !== true;
                });
            }

            if (is_string($listItem) && $this->equals($listItem)) {
                $item[$field] = null;
                return $item;
            }
        }

        if ($this->getOption('starts-with')) {
            if (is_array($listItem)) {
                $item[$this->options['key']] = array_filter($listItem, function ($itemValue) {
                    return $this->startsWith($itemValue) === true;
                });
            }

            if (is_string($listItem) && $this->startsWith($listItem)) {
                $item[$this->options['key']] = null;
                return $item;
            }
        }

        if ($this->getOption('not-starts-with')) {
            if (is_array($listItem)) {
                $item[$this->options['key']] = array_filter($listItem, function ($itemValue) {
                    return $this->notStartsWith($itemValue) === true;
                });
            }

            if (is_string($listItem) && $this->notStartsWith($listItem)) {
                $item[$this->options['key']] = null;
                return $item;
            }
        }

        return $item;
    }

    private function hasMatch($listItem): bool
    {
        return $this->options['case-sensitive'] ?
            strpos($listItem, $this->options['match']) === false :
            strpos(strtolower($listItem), strtolower($this->options['match'])) === false;
    }

    private function equals(string $listItem): bool
    {
        return $this->options['case-sensitive'] ?
            $listItem === $this->options['equals'] :
            strtolower($listItem) === strtolower($this->options['equals']);
    }

    private function startsWith(string $listItem): bool
    {
        $startsWith = $this->options['starts-with'];
        if ($this->options['case-sensitive']) {
            return strpos($listItem, $startsWith) === 0;
        } else {
            return stripos($listItem, $startsWith) === 0;
        }
    }

    private function notStartsWith(string $listItem): bool
    {
        $notStartsWith = $this->options['not-starts-with'];
        if ($this->options['case-sensitive']) {
            return strpos($listItem, $notStartsWith) !== 0;
        } else {
            return stripos($listItem, $notStartsWith) !== 0;
        }
    }
}