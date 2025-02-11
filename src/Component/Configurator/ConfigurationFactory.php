<?php

namespace Misery\Component\Configurator;

use App\Component\ChangeManager\ChangeManager;
use Psr\Log\LoggerInterface;
use Misery\Component\Common\FileManager\LocalFileManager;
use Misery\Component\Common\Pipeline\ActionPipe;
use Misery\Component\Common\Registry\Registry;
use Misery\Component\Common\Utils\ContextFormatter;
use Misery\Component\Common\Utils\ValueFormatter;

class ConfigurationFactory
{
    /** @var ConfigurationManager */
    private $manager;
    /** @var Configuration */
    private $config;
    private $factoryRegistry;

    public function __construct(Registry $factoryRegistry)
    {
        $this->factoryRegistry = $factoryRegistry;
    }

    public function getFactory(string $alias)
    {
        return $this->factoryRegistry->filterByAlias($alias);
    }

    public function init(
        LocalFileManager $workpath,
        LocalFileManager $source = null,
        LocalFileManager $additionalSources = null,
        LocalFileManager $extensions = null,
        LoggerInterface $logger
    ) {
        $this->config = new Configuration();
        $this->config->setLogger($logger);
        $sources = ($source) ? $this->getFactory('source')->createFromFileManager($source) : null;
        $this->manager = new ConfigurationManager(
            $this->config,
            $this,
            $workpath,
            $sources,
            $source,
            $additionalSources,
            $extensions
        );
    }

    public function setChangeManager(ChangeManager $changeManager)
    {
        $this->config->changeManager = $changeManager;
    }

    public function parseDirectivesFromConfiguration(array $configuration): Configuration
    {
        // sort the keys
        $order = [
            'aliases',
            'context',
            'account',
            'sources',
            'list',
            'mapping',
            'converter',
            'blueprint',
        ];

        $context = $configuration['context'];
        $configuration = ContextFormatter::format($context, $configuration);
        // we want to keep the original context data, it's the only part that should be excluded.
        $configuration['context'] = array_merge($context, $configuration['context']);

        // remove unused keys
        $order = array_filter($order, function ($orderItem) use ($configuration) {
            return key_exists($orderItem, $configuration);
        });
        $configuration = array_merge(array_flip($order), $configuration);

        $this->manager->getConfig()->clear();

        // level 0 directives only
        foreach ($configuration as $key => $valueConfiguration) {
            switch ($key) {
                case $key === 'context';
                    $this->manager->addContext($configuration['context']);
                    break;
                case $key === 'aliases';
                    // ValueFormatter converts %workpath% or other context params
                    $aliases = ValueFormatter::formatMulti($configuration['aliases'], $this->config->getContext());
                    $this->manager->getInMemoryFileManager()->addFromFileManager($this->manager->getWorkFileManager());
                    $this->manager->getInMemoryFileManager()->addAliases($aliases);
                    $this->manager->addSources(
                        iterator_to_array($this->manager->getInMemoryFileManager()->listFiles())
                    );
                    break;
                case $key === 'sources';
                    // ValueFormatter converts %workpath% or other context params
                    $sources = ValueFormatter::formatMulti($configuration['sources'], $this->config->getContext());
                    $this->manager->addSources($sources);
                    $this->manager->getInMemoryFileManager()->addFiles($sources);
                    break;
                case $key === 'account';
                    $this->manager->configureAccounts($configuration['account']);
                    break;
                case $key === 'transformation_steps';
                    $this->config->setAsMultiStep();
                    $this->config->getLogger()->info(sprintf("Multi Step [%s]", basename($this->config->getContext('transformation_file'))));
                    $this->manager->addTransformationSteps($configuration['transformation_steps'], $configuration);
                    break;
                case $key === 'pipeline';
                    $this->manager->configurePipelines($configuration['pipeline']);
                    break;
                case $key === 'shell';
                    $this->manager->configureShellCommands($configuration['shell']);
                    break;
                case $key === 'list';
                    $this->manager->createLists($configuration['list']);
                    break;
                case $key === 'mapping';
                    $this->manager->configureMapping($configuration['mapping']);
                    break;
                case $key === 'filter';
                    $this->manager->configureFilters($configuration['filter']);
                    break;
                case $key === 'converter';
                    $this->manager->configureConverters($configuration['converter']);
                    break;
                case $key === 'feed';
                    $this->manager->createFeed($configuration['feed']);
                    break;
                case $key === 'blueprint';
                    $this->manager->configureBlueprints($configuration['blueprint']);
                    break;

                case $key === 'group_action';
                case $key === 'action';
                case $key === 'actions';
                    $this->manager->configureAction($valueConfiguration);
                    break;
                case $key === 'encoder';
                    $this->manager->createEncoder($configuration['encoder']);
                    break;
                case $key === 'decoder';
                    $this->manager->createDecoder($configuration['decoder']);
                    break;
            }
        }

        return $this->config;
    }
}
