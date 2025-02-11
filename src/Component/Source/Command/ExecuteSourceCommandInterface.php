<?php

namespace Misery\Component\Source\Command;

use Misery\Component\Common\Options\OptionsInterface;

interface ExecuteSourceCommandInterface extends OptionsInterface
{
    public function execute();
    public function executeWithOptions(array $options);
}