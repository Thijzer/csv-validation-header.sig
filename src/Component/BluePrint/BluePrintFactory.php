<?php

namespace Misery\Component\BluePrint;

use Misery\Component\Common\Collection\ArrayCollection;
use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\ConfigurationManager;
use Symfony\Component\Yaml\Yaml;

class BluePrintFactory implements RegisteredByNameInterface
{
    private $bluePrintPath;

    public function __construct(string $bluePrintPath)
    {
        $this->bluePrintPath = $bluePrintPath;
    }

    public function createFromConfiguration(array $configuration, ConfigurationManager $configurationManager): ArrayCollection
    {
        $collection = new ArrayCollection();

        foreach ($configuration as $blueprintConf) {
            if (isset($blueprintConf['name'])) {
                $blueprint = $this->createFromName($blueprintConf['name'], $configurationManager, $blueprintConf);
                if (null === $blueprint) {
                    $blueprint = $this->createBlueprint($blueprintConf['name'], $blueprintConf, $configurationManager);
                }
                $collection->add($blueprint);
            }
        }

        return $collection;
    }

    public function createFromName(string $name, ConfigurationManager $configurationManager, array $configuration = []): ?BluePrint
    {
        // we check the configuration manager if we have this blueprint.
        $config = $configurationManager->getConfig();
        $blueprint = $config->getBlueprint($name);
        if ($blueprint) {
            return $blueprint;
        }

        $configDir = $this->bluePrintPath;
        if (dirname($name) === '.') {
            $name = basename($name);
            $transformationFile = $configurationManager->getConfig()->getContext('transformation_file');
            $configDir = dirname($transformationFile);
        }

        $configPath = sprintf('%s/%s.yaml', $configDir, $name);
        if (is_file($file = $configPath)) {
            // exception Config location not found
            return $this->createBlueprint($name, array_merge(Yaml::parseFile($file), $configuration), $configurationManager);
        }

        return null;
    }

    private function createBlueprint(string $name, array $configuration, ConfigurationManager $configurationManager): BluePrint
    {
        ## prepare the list commands
        if (isset($configuration['list'])) {
            $configurationManager->createLists($configuration['list']);
        }

        $converter = null;
        if (isset($configuration['converter'])) {
            $converter = $configurationManager->createConverter($configuration['converter']);
        }

        return new BluePrint(
            $name,
            $configurationManager->createEncoder($configuration),
            $configurationManager->createDecoder($configuration),
            $converter,
            $configuration['filenames'] ?? []
        );
    }

    public function getName(): string
    {
        return 'blueprint';
    }
}