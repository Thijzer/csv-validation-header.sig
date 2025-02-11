<?php

namespace Misery\Component\Item;

use Misery\Component\Statement\StatementBuilder;

class ItemsFactoryIntoItem
{
    public static function spreadFromConfig(
        ?array $rows,
        array $configuration
    ): array
    {
        if (empty($rows)) {
            return [];
        }

        $result = !empty($configuration['list']) ? array_fill_keys($configuration['list'], '') : [];
        $spreadConfiguration = $configuration['spread'];
        $attributesToKeep = [];

        if (isset($spreadConfiguration['keep_attributes'])) {
            $attributesToKeep = array_merge($attributesToKeep, $spreadConfiguration['keep_attributes']);
        }

        if (isset($spreadConfiguration['filter_list'])) {
            $attributesToKeep = array_merge($attributesToKeep, $spreadConfiguration['filter_list']);
        }

        $result = array_filter(
            $result,
            function($attrCode) use ($attributesToKeep) {
                if (empty($attributesToKeep)) {
                    return true;
                }

                return in_array($attrCode, $attributesToKeep, true);
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($rows as $row) {
            if (!isset($result[$configuration['on']]) || empty($result[$configuration['on']])) {
                $result[$configuration['on']] = $row[$configuration['on']];
            }

            if (!isset($row[$spreadConfiguration['attr_column']]) || !isset($row[$spreadConfiguration['value_column']])) {
                break;
            }

            $result[$row[$spreadConfiguration['attr_column']]] = $row[$spreadConfiguration['value_column']];
        }

        return $result;
    }

    public static function createFromConfig(array $items, array $configuration): array
    {
        $result = [];
        $main = [];
        foreach ($items as $item) {
            $main = $item;
            foreach ($configuration as $code => $options) {
                if (isset($options['when'])) {
                    $statement = StatementBuilder::buildFromOperator($options['when']['operator']);
                    $statement->when($options['when']['field'], $options['when']['state']);
                    if ($statement->isApplicable($item)) {
                        $result[$code][] = $item[$options['value']];
                    }
                    continue;
                }
                $result[$code][] = $item[$options['value']];
            }
        }

        return array_merge($result, $main);
    }
}