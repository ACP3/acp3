<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\NestedSet\Model;

use ACP3\Core\Model\AbstractModel;
use ACP3\Core\Model\DataProcessor;
use ACP3\Core\Model\Event\AfterModelDeleteEvent;
use ACP3\Core\Model\Event\AfterModelSaveEvent;
use ACP3\Core\Model\Event\BeforeModelDeleteEvent;
use ACP3\Core\Model\Event\BeforeModelSaveEvent;
use ACP3\Core\Model\SortingAwareInterface;
use ACP3\Core\NestedSet\Operation\Delete;
use ACP3\Core\NestedSet\Operation\Edit;
use ACP3\Core\NestedSet\Operation\Insert;
use ACP3\Core\NestedSet\Operation\Sort;
use ACP3\Core\Repository\AbstractRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property \ACP3\Core\NestedSet\Repository\NestedSetRepository $repository
 */
abstract class AbstractNestedSetModel extends AbstractModel implements SortingAwareInterface
{
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DataProcessor $dataProcessor,
        AbstractRepository $repository,
        protected Insert $insertOperation,
        protected Edit $editOperation,
        protected Delete $deleteOperation,
        private readonly Sort $sortOperation,
    ) {
        parent::__construct($eventDispatcher, $dataProcessor, $repository);
    }

    /**
     * @param array<string, mixed> $filteredNewData
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doUpsert(?int $entryId, array $filteredNewData): int
    {
        if (empty($entryId)) {
            return $this->insertOperation->execute($filteredNewData, $filteredNewData['parent_id'] ?: 0);
        }

        $this->editOperation->execute(
            $entryId,
            $filteredNewData['parent_id'],
            $filteredNewData[$this->repository::BLOCK_COLUMN_NAME] ?? 0,
            $filteredNewData
        );

        return $entryId;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function delete(array|int $entryId): int
    {
        if (!\is_array($entryId)) {
            $entryId = [$entryId];
        }

        $beforeDeleteEvent = new BeforeModelDeleteEvent(static::EVENT_PREFIX, $this->repository::TABLE_NAME, $entryId);
        $this->dispatchEvent($beforeDeleteEvent);

        // @deprecated since ACP3 version 6.11.0, to be removed with version 7.0.0. Subscribe to the `BeforeModelDeleteEvent` instead.
        $this->dispatchEvent($beforeDeleteEvent, 'core.model.before_delete');
        $this->dispatchEvent(
            $beforeDeleteEvent,
            static::EVENT_PREFIX . '.model.' . $this->repository::TABLE_NAME . '.before_delete'
        );

        $affectedRows = 0;
        foreach ($entryId as $item) {
            $affectedRows += (int) $this->deleteOperation->execute($item);
        }

        $afterDeleteEvent = new AfterModelDeleteEvent(static::EVENT_PREFIX, $this->repository::TABLE_NAME, $entryId);
        $this->dispatchEvent($afterDeleteEvent);

        // @deprecated since ACP3 version 6.11.0, to be removed with version 7.0.0. Subscribe to the `AfterModelDeleteEvent` instead.
        $this->dispatchEvent($afterDeleteEvent, 'core.model.after_delete');
        $this->dispatchEvent(
            $afterDeleteEvent,
            static::EVENT_PREFIX . '.model.' . $this->repository::TABLE_NAME . '.after_delete'
        );

        return $affectedRows;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function moveUp(int $id): void
    {
        $this->move($id, 'up');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function moveDown(int $id): void
    {
        $this->move($id, 'down');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function move(int $id, string $direction): void
    {
        $this->dispatchBeforeSaveEvent(
            $this->getRepository(),
            $this->createModelSaveEvent(
                BeforeModelSaveEvent::class,
                $id,
                false,
                true
            ),
        );

        $this->sortOperation->execute($id, $direction);

        $this->dispatchAfterSaveEvent(
            $this->getRepository(),
            $this->createModelSaveEvent(
                AfterModelSaveEvent::class,
                $id,
                false,
                true
            ),
        );
    }
}
