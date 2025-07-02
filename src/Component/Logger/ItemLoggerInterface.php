<?php

namespace Misery\Component\Logger;

use App\Component\Akeneo\Api\Objects\ReferencedItemInterface;
use App\Domain\Common\Model\OperationProgress;

interface ItemLoggerInterface
{
// This is App only section

//    public function logCreateItem(ReferencedItemInterface $entity): void;
//
//    public function logUpdateItem(ReferencedItemInterface $entity, string $updateMessage = ''): void;
//
//    public function logRemoveItem(ReferencedItemInterface $entity): void;
//
//    public function logFailedItem(ReferencedItemInterface $entity, string $failMessage): void;
//
//    public function logItem(ReferencedItemInterface $entity): void;

    /** @deprecated  */
    public function logCreate(string $entityClass, string $identifier): void;

    /** @deprecated  */
    public function logUpdate(string $entityClass, string $identifier, string $updateMessage = ''): void;

    /** @deprecated  */
    public function logRemove(string $entityClass, string $identifier): void;
    /** @deprecated  */
    public function logFailed(string $entityClass, string $identifier, string $failMessage): void;

    /** @deprecated  */
    public function log(string $entityClass, string $identifier): void;
}