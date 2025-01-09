<?php

namespace Misery\Component\Source;

interface SourceAwareInterface
{
    public function getSource(): Source;
    public function setSource(Source $source): void;
}