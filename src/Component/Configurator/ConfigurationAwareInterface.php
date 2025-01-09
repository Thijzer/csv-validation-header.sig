<?php

namespace Misery\Component\Configurator;

interface ConfigurationAwareInterface
{
    public function getConfiguration(): Configuration;
    public function setConfiguration(Configuration $configuration): void;
}