<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;
use Misery\Component\Common\Utils\ValueFormatter;
use Misery\Component\Mapping\ColumnMapper;

class GenerateIdAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'generate_id';

    private $collectedIds = [];

    /** @var array */
    private $options = [
        'start_id' => 1,
        'field' => null,
        'format_fields' => [],
        'format' => '%auto-increment%'
    ];

    public function apply(array $item): array
    {
        $format = $this->getOption('format');
        if (strpos($format, '%auto-increment%') !== false) {
            return $this->autoIncrement($item);
        }
        if (strpos($format, '%auto-sequence%') !== false) {
            return $this->autoSequence($item);
        }

        return $item;
    }

    private function autoSequence(array $item): array
    {
        $values = [];

        foreach ($this->getOption('format_fields') as $field) {
            $values[$field] = $item[$field] ?? null;
        }

        $key = implode('/', array_values($values));

        // getOption id is the start ID per from_field
        $values['auto-sequence'] = $this->collectedIds[$key] = $this->collectedIds[$key] ?? $this->getOption('start_id');
        $this->collectedIds[$key] = $this->collectedIds[$key] + 1;

        $item[$this->getOption('field')] = ValueFormatter::format($this->getOption('format'), $values);

        return $item;
    }

    private function autoIncrement(array $item): array
    {
        $id = $this->getOption('start_id');

        $item[$this->getOption('field')] = $id;

        $id = $id+1;
        $this->setOption('start_id', $id);

        return $item;
    }
}