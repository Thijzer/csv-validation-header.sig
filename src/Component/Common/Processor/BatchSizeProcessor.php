<?php

namespace Misery\Component\Common\Processor;

class BatchSizeProcessor
{
    private $batchSize;
    private $itemCount;
    private $batchPart = 1;
    private $index = 1;

    public function __construct(int $batchSize, int $itemCount = -1)
    {
        $this->itemCount = $itemCount;
        $this->batchSize = $batchSize;
    }

    public function getBatchPart(): int
    {
        $batchPart = $this->batchPart;
        if ($this->shouldBatchSize() && $this->hasNewBatchPart()) {
            $this->batchPart++;
        }

        return $batchPart;
    }

    public function hasNewBatchPart(): bool
    {
        return $this->shouldBatchSize() && $this->index % $this->getBatchSize() === 0;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    private function shouldBatchSize(): bool
    {
        return $this->itemCount === -1 || $this->itemCount > $this->getBatchSize();
    }

    public function next()
    {
        $this->index++;
    }
}
