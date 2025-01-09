<?php

namespace Misery\Component\Shell;

use Misery\Component\Common\Utils\ValueFormatter;

class ShellCommands
{
    private $commands = [];
    private $context;

    public function __construct(array $context)
    {
        $this->context = $context;
    }

    public function addCommand(string $name, string $command): void
    {
        $command = ValueFormatter::format($command, $this->context);
        $this->commands[$name] = $command;
    }

    public function addScript(string $name, string $script, array $args = [], string $executable = '/usr/bin/bash'): void
    {
        $script = (string) ValueFormatter::format($script, $this->context);

        $args = array_map(function ($argument) {
            return ValueFormatter::format($argument, $this->context);
        }, $args);

        $this->commands[$name] = implode(' ', array_merge(array_filter([$executable, $script]), $args));
    }

    public function exec(): void
    {
        foreach ($this->commands as $command) {
            $command = escapeshellcmd($command);
            $output = shell_exec($command);
        }
    }
}