<?php

namespace Misery\Component\Configurator;

use Misery\Component\Action\ItemActionProcessor;
use Misery\Component\BluePrint\BluePrint;
use Misery\Component\Common\Client\ApiClient;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Collection\ArrayCollection;
use Misery\Component\Common\Pipeline\Pipeline;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Feed\FeedInterface;
use Misery\Component\Reader\ItemReaderInterface;
use Misery\Component\Reader\ReaderInterface;
use Misery\Component\Shell\ShellCommands;
use Misery\Component\Source\SourceCollection;
use Misery\Component\Writer\ItemWriterInterface;

class ReadOnlyConfiguration
{
    private array $mappings = [];
    private array $filters = [];
    private SourceCollection $sources;
    private array $lists;

    public static function loadFromConfiguration(Configuration $configuration): self
    {
        $self = new self();
        $self->sources = $configuration->getSources();
        $self->lists = $configuration->getLists();
        $self->filters = $configuration->getFilters();
        $self->mappings = $configuration->getMappings();

        return $self;
    }

    public function getSources(): SourceCollection
    {
        return $this->sources;
    }

    public function getLists(): array
    {
        return array_map(function ($list) {
            return $list->getValues();
        }, $this->lists);
    }

    public function getList(string $alias)
    {
        return $this->lists[$alias]?->getValues();
    }

    public function getFilter(string $alias)
    {
        return $this->filters[$alias] ?? null;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getMappings(): array
    {
        return $this->getLists();
    }

    public function getMapping(string $alias)
    {
        return $this->getList($alias);
    }
}