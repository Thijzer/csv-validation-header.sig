<?php

namespace Misery\Component\Configurator;

use App\Component\ChangeManager\ChangeManager;
use Misery\Component\Action\ItemActionProcessorFactory;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Misery\Component\Action\ItemActionProcessor;
use Misery\Component\BluePrint\BluePrint;
use Misery\Component\Common\Client\ApiClient;
use Misery\Component\Common\Client\ApiClientInterface;
use Misery\Component\Common\Collection\ArrayCollection;
use Misery\Component\Common\FileManager\LocalFileManager;
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

class Configuration
{
    use LoggerAwareTrait;
    private $pipeline = null;
    private $actions = null;
    private $groupedActions = null;
    private $blueprints;
    private $encoders;
    private $decoders;
    private $reader;
    private $writer;
    /** @var ArrayCollection[]  $lists */
    private $lists = [];
    private $mappings = [];
    private $filters = [];
    private $converters;
    private $feeds;
    private $sources;
    private $context = [];
    private $shellCommands;
    /** @var ApiClient[] */
    private $accounts;
    private $isMultiStep = false;
    public ChangeManager $changeManager;
    public ItemActionProcessorFactory $actionFactory;

    private array $extensions = [];

    public function __construct()
    {
        $this->converters = new ArrayCollection();
        $this->feeds = new ArrayCollection();
        $this->encoders = new ArrayCollection();
        $this->decoders = new ArrayCollection();
        $this->blueprints = new ArrayCollection();
    }

    /**
     * @return \SplFileInfo[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function setExtensions(LocalFileManager $files): void
    {
        foreach ($files->listFiles() as $file) {
            $this->extensions[basename($file)] = new \SplFileInfo($file);
        }
    }

    public function isMultiStep(): bool
    {
        return $this->isMultiStep;
    }

    public function setAsMultiStep(): void
    {
        $this->isMultiStep = true;
    }

    public function addContext(array $context)
    {
        $this->context = array_merge($this->context, $context);
    }

    public function getContext(string $key = null)
    {
        return $key !== null ? $this->context[$key] ?? null: $this->context;
    }

    public function addSources(SourceCollection $sources): void
    {
        $this->sources = $sources;
    }

    public function getSources(): SourceCollection
    {
        return $this->sources;
    }

    public function addAccount(string $name, ApiClientInterface $apiClient)
    {
        $this->accounts[$name] = $apiClient;
    }

    public function getAccount(string $name): ?ApiClientInterface
    {
        return $this->accounts[$name] ?? null;
    }

    public function setPipeline(Pipeline $pipeline): void
    {
        $this->pipeline = $pipeline;
    }

    public function getPipeline(): ?Pipeline
    {
        return $this->pipeline;
    }

    public function setShellCommands(ShellCommands $commands): void
    {
        $this->shellCommands = $commands;
    }

    public function getShellCommands(): ?ShellCommands
    {
        return $this->shellCommands;
    }

    public function clearShellCommands(): void
    {
        $this->shellCommands = null;
    }

    public function setActions(ItemActionProcessor $actionProcessor): void
    {
        $this->actions = $actionProcessor;
    }

    public function setActionFactory(ItemActionProcessorFactory $factory): void
    {
        $this->actionFactory = $factory;
    }

    public function generateActionProcessor(array $actions): ItemActionProcessor
    {
        return $this->actionFactory->createFromConfiguration($actions, $this, $this->sources);
    }

    public function setGroupedActions(string $name, ItemActionProcessor $actionProcessor): void
    {
        $this->groupedActions[$name] = $actionProcessor;
    }

    public function getGroupedActions(string $name): ?ItemActionProcessor
    {
        return $this->groupedActions[$name] ?? null;
    }

    public function getActions(): ?ItemActionProcessor
    {
        return $this->actions;
    }

    public function addBlueprint(BluePrint $bluePrint): void
    {
        $this->blueprints->add($bluePrint);
    }

    public function getBlueprint(string $name)
    {
        return $this->blueprints->filter(function (RegisteredByNameInterface $blueprint) use ($name) {
            return $blueprint->getName() === $name;
        })->first();
    }

    public function getBlueprints(): ArrayCollection
    {
        return $this->blueprints;
    }

    public function addBlueprints(ArrayCollection $collection): void
    {
        $this->blueprints->merge($collection);
    }

    public function addLists(array $lists): void
    {
        foreach ($lists as $listName => $list) {
            $this->addList($listName, $list);
        }
    }

    public function addList(string $listName, array $list): void
    {
        $this->lists[$listName] = new ArrayCollection($list);
    }

    public function updateList(string $listName, array $list): void
    {
        /** @var ArrayCollection $currentList */
        $currentList = $this->getListObject($listName);
        if ($currentList === null) {
            $this->addList($listName, $list);
            return;
        }

        $currentList->purge();
        $currentList->addValues($list);
    }

    public function getLists(): array
    {
        return $this->lists;
    }

    public function getList(string $alias): ?array
    {
        if (!isset($this->lists[$alias])) {
            return null;
        }

        return $this->lists[$alias]->getValues();
    }

    public function addFilters(array $filters): void
    {
        $this->filters = array_merge($this->filters, $filters);
    }

    public function getFilter(string $alias)
    {
        return $this->filters[$alias] ?? null;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function addConverter(ConverterInterface|RegisteredByNameInterface $converter): void
    {
        $this->converters->set($converter->getName(), $converter);
    }

    public function getConverter(string $name): ?ConverterInterface
    {
        return $this->converters->filter(function (RegisteredByNameInterface $converter) use ($name) {
            return $converter->getName() === $name;
        })->first() ?: null;
    }

    public function addFeed(FeedInterface $feed): void
    {
        $this->feeds->add($feed);
    }

    public function getFeed(string $name): ?FeedInterface
    {
        return $this->feeds->filter(function (RegisteredByNameInterface $feed) use ($name) {
            return $feed->getName() === $name;
        })->first();
    }

    public function addDecoder(ItemDecoder $decoders): void
    {
        $this->decoders->add($decoders);
    }

    public function getDecoder(string $name): ?ItemDecoder
    {
        return $this->decoders->filter(function (RegisteredByNameInterface $encoder) use ($name) {
            return $encoder->getName() === $name;
        })->first();
    }

    public function setWriter(ItemWriterInterface $writer): void
    {
        $this->writer = $writer;
    }

    public function getWriter(): ?ItemWriterInterface
    {
        return $this->writer;
    }

    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function setReader($reader): void
    {
        if ($reader instanceof ReaderInterface) {
            $this->reader = $reader;
        }
    }

    public function addMapping(string $alias, array $mappings): void
    {
        $this->addLists([$alias => $mappings]);
    }

    public function getMappings(): array
    {
        return $this->getLists();
    }

    public function getMapping(string $alias)
    {
        return $this->getList($alias);
    }

    /**
     * @return ItemReaderInterface|ReaderInterface|null
     */
    public function getReader()
    {
        return $this->reader;
    }

    public function addEncoder(ItemEncoder $encoders): void
    {
        $this->encoders->add($encoders);
    }

    public function getEncoder(string $name): ?ItemEncoder
    {
        return $this->encoders->filter(function (RegisteredByNameInterface $encoder) use ($name) {
            return $encoder->getName() === $name;
        })->first();
    }

    public function clear(): void
    {
        $this->actions = null;

        if ($this->reader instanceof ItemReaderInterface) {
            $this->reader->clear();
            $this->reader = null;
        }

        $this->writer = null;
        $this->pipeline = null;
        $this->shellCommands = null;
        $this->converters = new ArrayCollection();
        $this->feeds = new ArrayCollection();
        $this->encoders = new ArrayCollection();
        $this->decoders = new ArrayCollection();
        $this->blueprints = new ArrayCollection();
    }

    private function getListObject(string $alias): ?ArrayCollection
    {
        return $this->lists[$alias] ?? null;
    }
}