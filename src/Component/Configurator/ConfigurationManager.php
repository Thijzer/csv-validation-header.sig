<?php

namespace Misery\Component\Configurator;

use Misery\Component\Action\ItemActionProcessorFactory;
use Misery\Component\BluePrint\BluePrint;
use Misery\Component\BluePrint\BluePrintFactory;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\Pipeline\PipelineFactory;
use Misery\Component\Converter\ConverterFactory;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Decoder\ItemDecoder;
use Misery\Component\Decoder\ItemDecoderFactory;
use Misery\Component\Encoder\ItemEncoder;
use Misery\Component\Encoder\ItemEncoderFactory;
use Misery\Component\Source\ListFactory;
use Misery\Component\Source\SourceCollection;
use Misery\Component\Source\SourceCollectionFactory;

class ConfigurationManager
{
    private $sources;
    private $manager;
    private $config;
    private $factory;

    public function __construct(
        Configuration $config,
        ConfigurationFactory $factory,
        SourceCollection $sources,
        LocalFileManager $manager
    ) {
        $this->sources = $sources;
        $this->manager = $manager;
        $this->config = $config;
        $this->factory = $factory;
    }

    /**
     * @return Configuration
     */
    public function getConfig(): Configuration
    {
        return $this->config;
    }

    public function addSources(array $configuration)
    {
        /** @var SourceCollectionFactory $factory */
        $factory = $this->factory->getFactory('source');
        $this->sources = $factory->createFromConfiguration($configuration, $this->sources);
    }

    public function createPipelines(array $configuration)
    {
        /** @var PipelineFactory $factory */
        $factory = $this->factory->getFactory('pipeline');
        $this->config->setPipeline(
            $factory->createFromConfiguration($configuration, $this->manager, $this)
        );
    }

    public function createActions(array $configuration)
    {
        /** @var ItemActionProcessorFactory $factory */
        $factory = $this->factory->getFactory('action');
        $actions = $factory->createActionProcessor($this->sources, $configuration);

        $this->config->setActions($actions);

        return $actions;
    }

    public function createConverter(array $configuration)
    {
        /** @var ConverterFactory $factory */
        $factory = $this->factory->getFactory('converter');
        $converter = $factory->createFromConfiguration($configuration, $this->config->getLists());

        $this->config->addConverter($converter);

        return $converter;
    }

    public function createEncoder(array $configuration, ConverterInterface $converter = null): ItemEncoder
    {
        /** @var ItemEncoderFactory $factory */
        $factory = $this->factory->getFactory('encoder');
        $encoder = $factory->createItemEncoder($configuration, $this, $converter);

        $this->config->addEncoder($encoder);

        return $encoder;
    }

    public function createDecoder(array $configuration, ConverterInterface $converter = null): ItemDecoder
    {
        /** @var ItemDecoderFactory $factory */
        $factory = $this->factory->getFactory('decoder');
        $decoder = $factory->createItemDecoder($configuration, $converter);

        $this->config->addDecoder($decoder);

        return $decoder;
    }

    public function createBlueprint($configuration): ?BluePrint
    {
        /** @var BluePrintFactory $factory */
        $factory = $this->factory->getFactory('blueprint');

        if (is_array($configuration)) {
            $blueprints = $factory->createFromConfiguration($configuration, $this);
            $this->config->addBlueprints($blueprints);
        }
        if (is_string($configuration)) {
            $blueprint = $factory->createFromName($configuration, $this);
            $this->config->addBlueprint($blueprint);

            return $blueprint;
        }
    }

    public function createLists(array $configuration)
    {
        /** @var ListFactory $factory */
        $factory = $this->factory->getFactory('list');
        $lists = $factory->createFromConfiguration($configuration, $this->sources);

        $this->config->addLists($lists);

        return $lists;
    }
}