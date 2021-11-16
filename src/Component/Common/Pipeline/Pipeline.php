<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;

class Pipeline
{
    /** @var PipeReaderInterface */
    private $in;
    /** @var PipeInterface[] */
    private $lines = [];
    /** @var PipeWriterInterface[] */
    private $out = [];

    public function input(PipeReaderInterface $reader): self
    {
        $this->in = $reader;

        return $this;
    }

    public function line(PipeInterface $pipe): self
    {
        $this->lines[] = $pipe;

        return $this;
    }

    public function output(PipeWriterInterface $writer): self
    {
        $this->out[] = $writer;

        return $this;
    }

    public function run(int $amount = -1)
    {
        $i = 0;
        while ($i !== $amount && $item = $this->in->read()) {
            try {
                foreach ($this->lines as $line) {
                    $item = $line->pipe($item);
                }
            } catch (SkipPipeLineException $exception) {
                continue;
            }
            foreach ($this->out as $out) {
                $out->write($item);
            }
            $i++;
        }
    }
}