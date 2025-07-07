<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Logger\ItemLoggerAwareTrait;
use Psr\Log\LoggerAwareTrait;
use Misery\Component\Common\Pipeline\Exception\InvalidItemException;
use Misery\Component\Common\Pipeline\Exception\SkipPipeLineException;
use Misery\Component\Debugger\ItemDebugger;
use Misery\Component\Debugger\NullItemDebugger;

class Pipeline
{
    use LoggerAwareTrait;
    use ItemLoggerAwareTrait;

    /** @var PipeReaderInterface */
    private $input;
    /** @var PipeWriterInterface */
    private $invalid;
    /** @var PipeInterface[] */
    private $pipeLines = [];
    /** @var PipeWriterInterface[] */
    private $outputs = [];
    /** @var NullItemDebugger */
    private $debugger;

    public function __construct()
    {
        $this->input = new NullPipeReader();
        $this->debugger = new NullItemDebugger();
    }

    public function input(PipeReaderInterface $reader): self
    {
        $this->input = $reader;

        return $this;
    }

    public function line(PipeInterface $pipe): self
    {
        $this->pipeLines[] = $pipe;

        return $this;
    }

    public function invalid(PipeWriterInterface $writer): self
    {
        $this->invalid = $writer;

        return $this;
    }

    public function output(PipeWriterInterface $writer): self
    {
        $this->outputs[] = $writer;

        return $this;
    }

    public function runInDebugMode(int $amount = -1, int $lineNumber = -1): void
    {
        $this->debugger = new ItemDebugger();
        $this->run($amount, $lineNumber);
    }

    private function handleException(InvalidItemException $exception, int $lineNumber): void
    {
        if ($exception->hasErrors()) {
            foreach ($exception->getErrors()->getErrorMessages() as $errorMessage) {
                $this->getItemLogger()->logFailed(
                    $exception->getInvalidIdentityClass(),
                    $exception->getInvalidIdentifier(),
                    $errorMessage
                );
            }
        }
        $this->invalid->write([
            'line' => $lineNumber,
            'msg' => $exception->getMessage(),
            'item' => json_encode($exception->getInvalidItemData()),
        ]);

        // WE need a silent LOGGER here
        //$this->logger->error($exception->getMessage());
        //$this->logger->error($exception->getMessage(), $exception->getInvalidItem());
    }

    public function run(int $amount = -1, int $lineNumber = -1): void
    {
        $i = 0;
        // looping
        while ($i !== $amount && $item = $this->input->read()) {
            $i++;
            if ($i !== $lineNumber && $lineNumber !== -1) {
                continue;
            }
            $this->debugger->log($item, 'original item');
            try {
                foreach ($this->pipeLines as $pipeLine) {
                    $item = $pipeLine->pipe($item);
                }
                foreach ($this->outputs as $output) {
                    $output->write($item);
                }
            } catch (SkipPipeLineException $exception) {
                if (!empty($exception->getMessage())) {
                    $this->logger->info(sprintf('Skipped: %s', $exception->getMessage()));
                }
                continue;
            } catch (InvalidItemException $exception) {
                $this->handleException($exception, $i);
                continue;
            }
            if ($i === $lineNumber) {
                break;
            }
        }
        unset($output);

        // stopping
        $this->input->stop();

        foreach ($this->outputs as $output) {
            try {
                $output->stop();
            } catch (SkipPipeLineException $exception) {
                continue;

            } catch (InvalidItemException $exception) {
                $this->handleException($exception, $i);
                continue;
            }
        }
    }
}