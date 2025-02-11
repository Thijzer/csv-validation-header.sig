<?php

namespace Misery\Component\Shell;

use Misery\Component\Common\Registry\RegisteredByNameInterface;
use Misery\Component\Configurator\Configuration;

class ShellCommandFactory implements RegisteredByNameInterface
{
    public function createFromConfiguration(array $configuration, Configuration $config): ShellCommands
    {
        $command = new ShellCommands($config->getContext());
        foreach ($configuration as $shellCommand) {
            if (isset($shellCommand['cmd'])) {
                $command->addCommand($shellCommand['name'], $shellCommand['cmd']);
                continue;
            }
            if (isset($shellCommand['script'])) {
                $command->addScript(
                    $shellCommand['name'],
                    $shellCommand['script'],
                    $shellCommand['script_args'] ?? [],
                    $shellCommand['executable'] ?? null
                );
            }
        }

        return $command;
    }

    public function getName(): string
    {
        return 'shell';
    }
}
