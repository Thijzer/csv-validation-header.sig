<?php

namespace Misery\Component\Configurator;

trait ConfigurationTrait
{
    /** @var Configuration */
    private $configuration;

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration): void
    {
        $this->configuration = $configuration;
    }
}