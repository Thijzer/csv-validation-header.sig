<?php

namespace Misery\Component\Parser;

use Assert\Assert;
use Misery\Component\Common\Cursor\ContinuousBufferFetcher;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\Cursor\FunctionalCursor;
use Misery\Component\Common\Cursor\OldCachedZoneFetcher;
use Misery\Component\Common\FileManager\InMemoryFileManager;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Filter\ColumnReducer;
use Misery\Component\Reader\ItemCollection;

class ItemParserFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(
        array $configuration,
        InMemoryFileManager $manager
    ) : CursorInterface {
        $type = strtolower($configuration['type']);
        Assert::that(
            $type,
            'type must be filled in.'
        )->notEmpty()->string()->inArray(['xml', 'csv', 'xlsx', 'list', 'feed', 'yaml', 'buffer', 'json', 'jsonl', 'json-file']);

        $fetchers = [
            'continuous' => ContinuousBufferFetcher::class,
            'zone' => OldCachedZoneFetcher::class
        ];
        $classFetcher = $fetchers[strtolower($configuration['fetcher'] ?? 'zone')];

        if (isset($configuration['join'])) {
            $joins = $configuration['join'];
            unset($configuration['join']);
            $mainParser = $this->createFromConfiguration($configuration, $manager);

            foreach ($joins as $join) {
                $fetcher = clone new $classFetcher($this->createFromConfiguration($join, $manager), $join['link_join'], $join['allow_fileindex_removal'] ?? false);
                $mainParser = new FunctionalCursor($mainParser, function ($row) use ($fetcher, $join) {
                    $masterID = $row[$join['link']];
                    $item = $fetcher->get($masterID) ?? [];

                    if (false === $item) {
                       $item = [];
                    }

                    // only reduce when necessary
                    if (!empty($join['return'])) {
                        $item = ColumnReducer::reduceItem($item, ...$join['return']);
                    }

                    // by using the + sign, we keep our array keys
                    return $row+$item;
                });
            }

            return $mainParser;
        }

        // TODO json and json-file are the same, we should merge them
        if ($type === 'json' || $type === 'buffer' || $type === 'jsonl') {
            return JsonParser::create(
                $manager->getFile($configuration['filename'])
            );
        }

        if ($type === 'json-file') {
            return JsonFileParser::create(
                $manager->getFile($configuration['filename']),
                $configuration['container'] ?? null
            );
        }

        if ($type === 'xml') {
            return XmlParser::create(
                $manager->getFile($configuration['filename']),
                $configuration['container'] ?? null
            );
        }
        if ($type === 'csv') {
            if (isset($configuration['encoding']) && $configuration['encoding'] === 'UTF8-BOM') {
                return CsvBomParser::create(
                    $manager->getFile($configuration['filename']),
                    $configuration['delimiter'] ?? CsvParser::DELIMITER,
                    $configuration['enclosure'] ?? CsvParser::ENCLOSURE,
                    $configuration['escape'] ?? CsvParser::ESCAPE,
                    $configuration['invalid_lines'] ?? CsvParser::INVALID_STOP
                );
            }

            return CsvParser::create(
                $manager->getFile($configuration['filename']),
                $configuration['delimiter'] ?? CsvParser::DELIMITER,
                $configuration['enclosure'] ?? CsvParser::ENCLOSURE,
                $configuration['escape'] ?? CsvParser::ESCAPE,
                $configuration['invalid_lines'] ?? CsvParser::INVALID_STOP
            );
        }
        if ($type === 'yaml') {
            return YamlParser::create(
                $manager->getFile($configuration['filename']),
            );
        }
        if ($type === 'xlsx') {
            return XlsxParser::create(
                $manager->getFile($configuration['filename']),
            );
        }
        if (in_array($type, ['list', 'feed'])) {
            return new ItemCollection();
        }

        throw new \RuntimeException('Impossible Exception');
    }

    public function getName(): string
    {
        return 'parser';
    }
}