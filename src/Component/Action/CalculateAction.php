<?php

namespace Misery\Component\Action;

use Misery\Component\Common\Options\OptionsInterface;
use Misery\Component\Common\Options\OptionsTrait;

class CalculateAction implements OptionsInterface
{
    use OptionsTrait;

    public const NAME = 'calculate';

    /** @var array */
    private $options = [
        'fields' => [],
        'operator' => '',
        'result' => ''
    ];

    public function apply(array $item): array
    {
        $fields = $this->options['fields'];
        $operator = $this->options['operator'];
        $result = $this->options['result'];

        // validation
        if (!is_array($fields)) {
            return $item;
        }

        foreach ($fields as $field) {
            if(isset($item[$field]) && is_numeric($item[$field])) {
                $numbers[] = $item[$field];
            }
        }

        if (!isset($numbers) || !is_array($numbers)) {
            return $item;
        }

        switch($operator) {
            case 'ADD':
                $tmp = array_sum($numbers);
                break;
            case 'MULTIPLY':
                $tmp = array_product($numbers);
                break;
            case 'DIVIDE':
                $tmp = $this->divide($numbers);
                break;
            case 'SUBTRACT':
                $tmp = $this->subtract($numbers);
                break;
            default:
                $tmp = 0;
                break;
        }

        $item[$result] = $tmp;

        return $item;
    }

    private function subtract(array $numbers): float
    {
        while (sizeof($numbers) > 1) {
            $tmp = array_shift($numbers) - array_shift($numbers);
            array_unshift($numbers, $tmp);
        }
        return $numbers[0];
    }

    private function divide(array $numbers): float
    {
        while (sizeof($numbers) > 1) {
            $tmp = array_shift($numbers) / array_shift($numbers);
            array_unshift($numbers, $tmp);
        }
        return $numbers[0];
    }
}
