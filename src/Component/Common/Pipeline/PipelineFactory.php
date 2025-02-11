<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Common\Cursor\FunctionalCursor;
use Misery\Component\Common\Cursor\SubItemCursor;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationManager;
use Misery\Component\Converter\ConverterInterface;
use Misery\Component\Converter\InitConverterInterface;
use Misery\Component\Converter\ItemCollectionLoaderInterface;
use Misery\Component\Reader\ItemReader;
use Misery\Component\Writer\ItemWriterInterface;

class PipelineFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(
        array $configuration,
        ConfigurationManager $configurationManager
    ): Pipeline {
        $pipeline = $this->getPipeLine(
            $configurationManager,
            $configuration
        );

        foreach ($configuration as $key => $valueConfiguration) {
            switch (true) {
                case $key === 'output' && isset($configuration['output']['http']):
                    $writer = $configurationManager->createHTTPWriter($configuration['output']['http']);

                    if (isset($configuration['output']['http']['converter'])) {
                        $converter = $configurationManager->createConverter($configuration['output']['http']['converter']);
                        $pipeline->line(new RevertPipe($converter));
                    }

                    if (isset($configuration['output']['http']['buffer_file'])) {
                        $pipeline->output(new PipeWriter(
                            $configurationManager->createBufferWriter($configuration['output']['http']['buffer_file'])
                        ));
                        break;
                    }

                    $pipeline->output(new PipeWriter($writer));

                    $pipeline->invalid(
                        new PipeWriter($this->createInvalid($configurationManager, $configuration))
                    );
                    break;

                case $key === 'output' && isset($configuration['output']['writer']):
                    $writer = $configurationManager->createWriter($configuration['output']['writer']);

                    if (isset($configuration['output']['writer']['converter'])) {
                        $converter = $configurationManager->createConverter($configuration['output']['writer']['converter']);
                        $pipeline->line(new RevertPipe($converter));
                    }

                    $pipeline->output(new PipeWriter($writer));

                    $pipeline->invalid(
                        new PipeWriter($this->createInvalid($configurationManager, $configuration))
                    );
                    break;

                case $key === 'blueprint';
                    $blueprint = $configurationManager->createBlueprint($configuration['blueprint']);
                    $pipeline->line(new EncodingPipe($blueprint->getEncoder()));
                    if ($blueprint->getConverter()) {
                        $pipeline->line(new ConverterPipe($blueprint->getConverter()));
                        $pipeline->line(new RevertPipe($blueprint->getConverter()));
                    }

                    $pipeline->line(new DecodingPipe($blueprint->getDecoder()));
                    // what about decode and revert
                    break;
                case $key === 'converter':
                    $converter = $configurationManager->createConverter($configuration['converter']);
                    $pipeline->line(new ConverterPipe($converter));
                    break;
                case $key === 'actions';
                    $actions = $configurationManager->createActions($configuration['actions']);
                    $pipeline->line(new ActionPipe($actions));
                    break;
                case $key === 'decoder';
                    $decoder = $configurationManager->createDecoder($configuration['decoder']);
                    $pipeline->line(new DecodingPipe($decoder));
                    break;
                case $key === 'encoder';
                    $encoder = $configurationManager->createEncoder($configuration['encoder']);
                    $pipeline->line(new EncodingPipe($encoder));
                    break;
            }
        }

        return $pipeline;
    }

    private function createInvalid(ConfigurationManager $configurationManager, array $configuration): ItemWriterInterface
    {
        $configuration['output']['writer']['filename'] = 'invalid_items.csv';
        $configuration['output']['writer']['type'] = 'csv';
        $configuration['output']['writer']['format']['mode'] = 'append';

        return $configurationManager->createWriter($configuration['output']['writer']);
    }

    private function getPipeLine(
        ConfigurationManager $configurationManager,
        array $configuration
    ): Pipeline
    {
        $configuration = $configuration['input'];
        $pipeline = new Pipeline();

        $reader = (isset($configuration['http'])) ?
            $configurationManager->createHTTPReader($configuration['http']) :
            $configurationManager->createReader($configuration['reader'])
        ;
        if (isset($configuration['reader']['converter'])) {
            $converter = $configurationManager->createConverter($configuration['reader']['converter']);
            if ($converter instanceof InitConverterInterface) {
                $converter->init();
            }
            if ($converter instanceof ItemCollectionLoaderInterface) {
                $reader = new ItemReader(new SubItemCursor($reader->getCursor(), $converter));
            } else {
                $reader = new ItemReader(new FunctionalCursor($reader->getCursor(), function ($item) use ($converter)  {
                    return $converter->convert($item);
                }));
            }
        }

        $pipeline->input(new PipeReader($reader));

        return $pipeline;
    }

    public function getName(): string
    {
        return 'pipeline';
    }
}