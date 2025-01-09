<?php

namespace Misery\Component\Configurator;

use Assert\Assert;
use Assert\Assertion;
use Misery\Component\Action\ItemActionProcessor;
use Misery\Component\Action\ItemActionProcessorFactory;
use Misery\Component\Akeneo\Client\HttpWriterFactory;
use Misery\Component\BluePrint\BluePrint;
use Misery\Component\BluePrint\BluePrintFactory;
use Misery\Component\Common\Client\ApiClientFactory;
use Misery\Component\Common\Cursor\CursorFactory;
use Misery\Component\Common\Cursor\CursorInterface;
use Misery\Component\Common\FileManager\FileManagerInterface;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\FileManager\InMemoryFileManager;
use Misery\Component\Common\Functions\ArrayFunctions;
use Misery\Component\Common\Pipeline\PipelineFactory;
use Misery\Component\Converter\ConverterFactory;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Feed\FeedFactory;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Feed\FeedInterface;
use Misery\Component\Mapping\MappingFactory;
use Misery\Component\Parser\ItemParserFactory;
use Misery\Component\Process\ProcessManager;
use Misery\Component\Project\ProjectDirectories;
use Misery\Component\Reader\ItemCollection;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Reader\ItemReaderFactory;
use Misery\Component\Reader\ReaderInterface;
use Misery\Component\Shell\ShellCommandFactory;
use Misery\Component\Source\ListFactory;
use Misery\Component\Source\SourceCollection;
use Misery\Component\Source\SourceCollectionFactory;
use Misery\Component\Source\SourceFilterFactory;
use Misery\Component\Writer\ItemWriterFactory;
use Misery\Component\Writer\ItemWriterInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationManager
{
    private $sources;
    private $sourceFiles;
    private $config;
    private $factory;
    private $workFiles;
    /** @var FileManagerInterface|InMemoryFileManager  */
    private $inMemoryFileManager;

    public function __construct(
        Configuration        $config,
        ConfigurationFactory $factory,
        LocalFileManager     $workFiles,
        SourceCollection     $sources = null,
        LocalFileManager     $sourceFiles = null,
        LocalFileManager     $additionalSources = null,
        LocalFileManager     $extensions = null,
    ) {
        $this->sources = $sources;
        $this->sourceFiles = $sourceFiles;
        $this->config = $config;
        $this->factory = $factory;
        $this->workFiles = $workFiles;

        $this->inMemoryFileManager = new InMemoryFileManager();
        if ($this->sourceFiles) {
            $this->inMemoryFileManager->addFromFileManager($this->sourceFiles);
        }

        if ($additionalSources) {
            $this->inMemoryFileManager->addFromFileManager($additionalSources);
            $this->addSources(
                iterator_to_array($this->getInMemoryFileManager()->listFiles())
            );
        }

        if ($extensions) {
            $this->config->setExtensions($extensions);
        }
    }

    /**
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function getWorkFileManager(): LocalFileManager
    {
        return $this->workFiles;
    }

    public function getInMemoryFileManager(): InMemoryFileManager
    {
        return $this->inMemoryFileManager;
    }

    public function addSources(array $configuration): void
    {
        /** @var SourceCollectionFactory $factory */
        $factory = $this->factory->getFactory('source');
        $this->sources = $factory->createFromConfiguration($configuration, $this->sources);

        /** @var BluePrint $blueprint */
        foreach ($this->config->getBlueprints()->getValues() as $blueprint) {
            $factory->createFromBluePrint($blueprint, $this->sources);
        }

        $this->config->addSources($this->sources);
    }

    public function addContext(array $configuration): void
    {
        $this->config->addContext($configuration);
    }

    public function addTransformationSteps(array $transformationSteps, array $masterConfiguration): void
    {
        /** @var ProjectDirectories $projectDirectories */
        $projectDirectories = $this->factory->getFactory('project_directories');
        $debug = $this->config->getContext('debug');
        $dirName = pathinfo($this->config->getContext('transformation_file'))['dirname'] ?? null;

        unset($masterConfiguration['transformation_steps']);

        # list of transformations
        foreach ($transformationSteps as $transformationFile) {

            // this code detects the run | with option
            // and creates virtual steps, so you don't need to repeat yourself
            if (isset($transformationFile['run'])) {
                $file = $transformationFile['run'];
                $withArray = $transformationFile['with'];

                // Get the number of iterations needed
                $iterationCount = count(current($withArray));

                // Iterate over each index
                for ($i = 0; $i < $iterationCount; $i++) {
                    $context = [];

                    // Build the context for the current index
                    foreach ($withArray as $key => $values) {
                        if (isset($values[$i])) {
                            $context[$key] = $values[$i];

                            // Save the context in the master configuration
                            $masterConfiguration['context'][$key] = $values[$i];
                        }
                    }

                    $this->addTransformationSteps([$file], $masterConfiguration);
                }
                continue;
            }

            $file = $dirName . DIRECTORY_SEPARATOR . $transformationFile;
            if (!is_file($file) && $projectDirectories->getTemplatePath()->isFile($transformationFile)) {
                $file = $projectDirectories->getTemplatePath()->getAbsolutePath($transformationFile);
            } else {
                Assertion::file($file);
            }

            // we need to start a new configuration manager.
            $transformationFile = ArrayFunctions::array_filter_recursive(Yaml::parseFile($file), function ($value) {
                return $value !== NULL;
            });
            $configuration = array_replace_recursive($masterConfiguration, $transformationFile, [
                'context' => [
                    'try' => $transformationFile['context']['try'] ?? null,
                    'debug' => $debug,
                    'dirname' => $dirName,
                    'transformation_file' => $file,
            ]]);
            $configuration = $this->factory->parseDirectivesFromConfiguration($configuration);

            // only start the process if our transformation file has a pipeline
            if (!isset($transformationFile['pipeline']) && !isset($transformationFile['shell'])) {
                continue;
            }

            (new ProcessManager($configuration))->startProcess();

            // TODO connect the outputs here
            if ($shellCommands = $configuration->getShellCommands()) {
                $shellCommands->exec();
                $configuration->clearShellCommands();
            }
        }
    }

    public function configureShellCommands(array $configuration)
    {
        /** @var ShellCommandFactory $factory */
        $factory = $this->factory->getFactory('shell');
        $this->config->setShellCommands(
            $factory->createFromConfiguration($configuration, $this->config)
        );
    }

    public function configurePipelines(array $configuration): void
    {
        /** @var PipelineFactory $factory */
        $factory = $this->factory->getFactory('pipeline');
        $this->config->setPipeline(
            $factory->createFromConfiguration($configuration, $this)
        );
    }

    public function configureAccounts(array $configuration): void
    {
        /** @var ApiClientFactory $factory */
        $factory = $this->factory->getFactory('api_client');
        foreach ($configuration as $account) {
            $this->config->addAccount($account['name'], $factory->createFromConfiguration($account));
        }
    }

    public function configureAction(array $configuration): void
    {
        foreach ($configuration as $action) {
            $this->config->setGroupedActions(
                $action['name'],
                $this->createActions($action['actions'])
            );
        }
    }

    public function createActions(array $configuration): ItemActionProcessor
    {
        /** @var ItemActionProcessorFactory $factory */
        $factory = $this->factory->getFactory('action');
        $actions = $factory->createFromConfiguration($configuration, $this->config, $this->sources);

        $this->config->setActions($actions);
        $this->config->setActionFactory($factory);

        return $actions;
    }

    public function configureConverters(array|string $configuration): void
    {
        if (isset($configuration[0]['name'])) {
            foreach ($configuration as $converter) {
                $this->createConverter($converter);
            }
        } else {
            $this->createConverter($configuration);
        }
    }

    public function createConverter(array|string $configuration): ConverterInterface
    {
        $converter = null;
        /** @var ConverterFactory $factory */
        $factory = $this->factory->getFactory('converter');

        // stop creation if it was already created
        if (is_array($configuration) && isset($configuration['name'])) {
            if ($converter = $this->config->getConverter($configuration['name'])) {
                return $converter;
            }
        }
        if (is_string($configuration)) {
            if ($converter = $this->config->getConverter($configuration)) {
                return $converter;
            }
        }

        if (is_string($configuration)) {
            $converter = $factory->getConverterFromRegistry($configuration);
        }
        if (is_array($configuration) && isset($configuration['name'])) {
            $converter = $factory->createFromConfiguration($configuration, $this->config);
        }
        if ($converter) {
            $this->config->addConverter($converter);
            return $converter;
        }

        // code smell this looks a bad practice
        if (is_array($configuration) && isset($configuration[0])) {
            $factory->createMultipleConfigurations($configuration, $this->config);
        }

        return $converter;
    }

    public function createFeed(array $configuration): FeedInterface
    {
        /** @var FeedFactory $factory */
        $factory = $this->factory->getFactory('feed');
        $feed = $factory->createFromConfiguration($configuration, $this->config);

        $this->config->addFeed($feed);

        return $feed;
    }

    public function createEncoder(array $configuration): ItemEncoder
    {
        /** @var ItemEncoderFactory $factory */
        $factory = $this->factory->getFactory('encoder');
        $encoder = $factory->createFromConfiguration($configuration, $this);

        $this->config->addEncoder($encoder);

        return $encoder;
    }

    public function createDecoder(array $configuration): ItemDecoder
    {
        /** @var ItemDecoderFactory $factory */
        $factory = $this->factory->getFactory('decoder');
        $decoder = $factory->createFromConfiguration($configuration, $this);

        $this->config->addDecoder($decoder);

        return $decoder;
    }

    public function configureBlueprints(array $configuration): void
    {
        /** @var BluePrintFactory $factory */
        $factory = $this->factory->getFactory('blueprint');

        $blueprints = $factory->createFromConfiguration($configuration, $this);
        $this->config->addBlueprints($blueprints);
    }

    public function createBlueprint($configuration): ?BluePrint
    {
        /** @var BluePrintFactory $factory */
        $factory = $this->factory->getFactory('blueprint');
        $blueprint = null;
        if (is_string($configuration)) {
            $blueprint = $factory->createFromName($configuration, $this);
        }

        if (is_array($configuration)) {
            $blueprint = $factory->createFromConfiguration($configuration, $this);
        }

        if ($blueprint) {
            $this->config->addBlueprint($blueprint);
        }

        return $blueprint;
    }

    public function configureMapping(array $configuration): void
    {
        /** @var MappingFactory $factory */
        $factory = $this->factory->getFactory('mapping');
        $factory->createFromConfiguration($configuration, $this->inMemoryFileManager, $this);
    }

    public function createHTTPReader(array $configuration): ReaderInterface
    {
        $factory = $this->factory->getFactory('http_reader');
        $reader = $factory->createFromConfiguration($configuration, $this->config);

        $this->config->setReader($reader);

        return $reader;
    }

    public function createHTTPWriter(array $configuration): ItemWriterInterface
    {
        /** @var HttpWriterFactory $factory */
        $factory = $this->factory->getFactory('http_writer');
        $writer = $factory->createFromConfiguration($configuration, $this->config);

        $this->config->setWriter($writer);

        return $writer;
    }

    public function createWriter(array $configuration): ItemWriterInterface
    {
        /** @var ItemWriterFactory $factory */
        $factory = $this->factory->getFactory('writer');
        $writer = $factory->createFromConfiguration($configuration, $this->getWorkFileManager());

        $this->config->setWriter($writer);

        return $writer;
    }

    public function createBufferWriter(string $bufferFilename): ItemWriterInterface
    {
        return $this->createWriter([
            'type' => 'buffer',
            'filename' => $bufferFilename,
        ]);
    }

    public function createCursableParser(array $configuration): CursorInterface
    {
        /** @var ItemParserFactory $factory */
        $factory = $this->factory->getFactory('parser');
        $parser = $factory->createFromConfiguration($configuration, $this->inMemoryFileManager);

        if (isset($configuration['cursor'])) {
            $parser = $this->createConnectedCursor($configuration['cursor'], $parser);
        }

        if ($parser instanceof ItemCollection && $configuration['type'] === 'list') {

            $list = $this->config->getList($configuration['list']);
            // list tech
            if (isset($configuration['create_item'])) {
                $items = [];
                foreach ($list as $listItem) {
                    $items[] = array_map(function ($item) use ($listItem) {
                        if ($item === '<list_item>') {
                            return $listItem;
                        }
                    }, $configuration['create_item']);
                }
                $parser->add($items);
            } else {
                $parser->add(
                    $this->config->getList($configuration['list'])
                );
            }
        }

        if ($parser instanceof ItemCollection && $configuration['type'] === 'feed') {
            $parser->add(
                $this->config->getFeed($configuration['name'])->feed()
            );
        }

        return $parser;
    }

    /**
     * @return ItemReader|ReaderInterface
     */
    public function createReader(array $configuration)
    {
        $cursor = $this->createCursableParser($configuration);

        /** @var ItemReaderFactory $factory */
        $factory = $this->factory->getFactory('reader');
        $reader = $factory->createFromConfiguration($cursor, $configuration, $this->getConfig());

        $this->config->setReader($reader);

        return $reader;
    }

    public function createConnectedCursor($configuration, CursorInterface $cursor): CursorInterface
    {
        if (is_array($configuration)) {
            foreach ($configuration as $confName) {
                $cursor = $this->createConnectedCursor($confName, $cursor);
            }
            return $cursor;
        }

        /** @var string $configuration */
        Assert::that($configuration)->string();

        /** @var CursorFactory $factory */
        $factory = $this->factory->getFactory('cursor');

        return $factory->createFromName($configuration, $cursor);
    }

    public function configureFilters(array $configuration): void
    {
        /** @var SourceFilterFactory $factory */
        $factory = $this->factory->getFactory('filter');
        $filters = $factory->createFromConfiguration($configuration, $this->sources);

        $this->config->addFilters($filters);
    }

    public function createLists(array $configuration): array
    {
        /** @var ListFactory $factory */
        $factory = $this->factory->getFactory('list');
        $lists = $factory->createFromConfiguration($configuration, $this->sources);

        $this->config->addLists($lists);

        return $lists;
    }
}