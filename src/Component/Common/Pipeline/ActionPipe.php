<?php

namespace Misery\Component\Common\Pipeline;

use Misery\Component\Action\ItemActionProcessor;
use Misery\Model\DataStructure\ItemInterface;

class ActionPipe implements PipeInterface
{
    public function __construct(private readonly ItemActionProcessor $actionProcessor) {}

    public function pipe(array $item): array
    {
        $item = $this->actionProcessor->process($item);

        // @todo current situation at the end of the pipeline
        // an improved solution would be to return an ItemInterface so we could have more context during conversion
        if ($item instanceof ItemInterface) {
            return $item->toArray();
        }

        return $item;
    }
}