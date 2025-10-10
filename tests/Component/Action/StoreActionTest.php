<?php

namespace Tests\Misery\Component\Action;

use App\Component\ChangeManager\ChangeManager;
use Misery\Component\Action\ItemActionProcessor;
use Misery\Component\Action\StoreAction;
use Misery\Component\Configurator\Configuration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StoreActionTest extends TestCase
{
    private Configuration|MockObject $configuration;
    private ChangeManager|MockObject $changeManager;

    protected function setUp(): void
    {
        if (!class_exists(\App\Component\ChangeManager\ChangeManager::class)) {
            $this->markTestSkipped('ChangeManager class does not exist.');
        }
        $this->configuration = $this->createMock(Configuration::class);
        $this->changeManager = $this->createMock(ChangeManager::class);

        $this->configuration->changeManager = $this->changeManager;
    }

    public function testDetectAddedChanges()
    {
        $item = [
            'identifier' => '123',
            'values' => ['name' => 'New Product'],
        ];

        $this->changeManager->method('hasChanges')->willReturn(true);
        $this->changeManager->method('getChanges')->willReturn([
            'added' => ['name'],
            'deleted' => [],
            'updated' => [],
            'all' => ['name'],
        ]);

        $this->configuration
            ->method('updateList')
            ->willReturnCallback(function (...$args) use (&$calls) {
                $calls[] = $args;
            }
        );

        $action = new StoreAction();
        $action->setConfiguration($this->configuration);
        $action->setOptions([
            'event' => StoreAction::PRODUCT_HAS_CHANGES,
            'identifier' => 'identifier',
            'entity' => 'product',
        ]);

        $action->apply($item);

        $this->assertCount(5, $calls);

        $this->assertContains(
            ['product_changes_fields_added', ['name']],
            $calls
        );
    }

    public function testDetectDeletedChanges()
    {
        $item = [
            'identifier' => '123',
            'values' => [],
        ];

        $this->changeManager->method('hasChanges')->willReturn(true);
        $this->changeManager->method('getChanges')->willReturn([
            'added' => [],
            'deleted' => ['name'],
            'updated' => [],
            'all' => ['name'],
        ]);


        $this->configuration
            ->method('updateList')
            ->willReturnCallback(function (...$args) use (&$calls) {
                $calls[] = $args;
            }
        );

        $action = new StoreAction();
        $action->setConfiguration($this->configuration);
        $action->setOptions([
            'event' => StoreAction::PRODUCT_HAS_CHANGES,
            'identifier' => 'identifier',
            'entity' => 'product',
        ]);

        $action->apply($item);

        $this->assertCount(5, $calls);

        $this->assertContains(
            ['product_changes_fields_deleted', ['name']],
            $calls
        );
    }

    public function testDetectUpdatedChanges()
    {
        $item = [
            'identifier' => '123',
            'values' => ['name' => 'Updated Product'],
        ];

        $this->changeManager->method('hasChanges')->willReturn(true);
        $this->changeManager->method('getChanges')->willReturn([
            'added' => [],
            'deleted' => [],
            'updated' => ['name'],
            'all' => ['name'],
        ]);

        $this->configuration
            ->method('updateList')
            ->willReturnCallback(function (...$args) use (&$calls) {
                $calls[] = $args;
            });

        $action = new StoreAction();
        $action->setConfiguration($this->configuration);
        $action->setOptions([
            'event' => StoreAction::PRODUCT_HAS_CHANGES,
            'identifier' => 'identifier',
            'entity' => 'product',
            'true_action' => ['some_action'],
        ]);

        $trueActionProcessor = $this->createMock(ItemActionProcessor::class);
        $trueActionProcessor->expects($this->once())
            ->method('process')
            ->with($item);

        $this->configuration->expects($this->once())->method('generateActionProcessor')
            ->willReturn($trueActionProcessor);

        $action->apply($item);

        $this->assertCount(5, $calls);

        $this->assertContains(
            ['product_changes_fields_updated', ['name']],
            $calls
        );
        $this->assertContains(
            ['product_changes_fields_deleted', []],
            $calls
        );
        $this->assertContains(
            ['product_changes_fields_added', []],
            $calls
        );
    }
}