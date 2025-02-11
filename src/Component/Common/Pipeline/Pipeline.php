<?php

namespace Misery\Component\Common\Pipeline;

use Psr\Log\LoggerAwareTrait;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use Misery\Component\Debugger\ItemDebugger;
use Misery\Component\Debugger\NullItemDebugger;

class Pipeline
{
    use LoggerAwareTrait;

    /** @var PipeReaderInterface */
    private $in;
    /** @var PipeWriterInterface */
    private $invalid;
    /** @var PipeInterface[] */
    private $lines = [];
    /** @var PipeWriterInterface[] */
    private $out = [];
    /** @var NullItemDebugger */
    private $debugger;

    public function input(PipeReaderInterface $reader): self
    {
        $this->in = $reader;
        $this->debugger = new NullItemDebugger();

        return $this;
    }

    public function line(PipeInterface $pipe): self
    {
        $this->lines[] = $pipe;

        return $this;
    }

    public function invalid(PipeWriterInterface $writer): self
    {
        $this->invalid = $writer;

        return $this;
    }

    public function output(PipeWriterInterface $writer): self
    {
        $this->out[] = $writer;

        return $this;
    }

    public function runInDebugMode(int $amount = -1, int $lineNumber = -1)
    {
        $this->debugger = new ItemDebugger();
        $this->run($amount, $lineNumber);
    }

    public function run(int $amount = -1, int $lineNumber = -1)
    {
        $i = 0;
        // looping
        while ($i !== $amount && $item = $this->in->read()) {
            $i++;
            if ($i !== $lineNumber && $lineNumber !== -1) {
                continue;
            }
            $this->debugger->log($item, 'original item');
            try {
                foreach ($this->lines as $line) {
                    $item = $line->pipe($item);
                }
                foreach ($this->out as $out) {
                    $out->write($item);
                }
            } catch (SkipPipeLineException $exception) {
                if (!empty($exception->getMessage())) {
                    $this->logger->info(sprintf('Skipped: %s', $exception->getMessage()));
                }
                continue;
            } catch (InvalidItemException $exception) {
                $this->invalid->write([
                    'line' => $i,
                    'msg' => $exception->getMessage(),
                    'item' => json_encode($exception->getInvalidItem()),
                ]);
                // WE need a silent LOGGER here
                //$this->logger->error($exception->getMessage(), $exception->getInvalidItem());
                continue;
            }
            if ($i === $lineNumber) {
                break;
            }
        }

        // stopping
        $this->in->stop();

        foreach ($this->out as $out) {
            try {
                $out->stop();
            } catch (SkipPipeLineException $exception) {
                continue;

            } catch (InvalidItemException $exception) {
                $this->invalid->write([
                    'line' => $i,
                    'msg' => $exception->getMessage(),
                    'item' => json_encode($exception->getInvalidItem()),
                ]);
                $this->logger->error($exception->getMessage());
                continue;
            }
        }
    }
}