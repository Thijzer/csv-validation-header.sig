<?php

namespace Misery\Component\Source;

trait SourceTrait
{
    /** @var Source */
    private $source;

    public function getSource(): Source
    {
        return $this->source;
    }

    public function setSource(Source $source): void
    {
        $this->source = $source;
    }
}