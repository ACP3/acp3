<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model;

use ACP3\Core\Helpers\Sort;
use ACP3\Core\Model\Event\AbstractModelSaveEvent;
use ACP3\Core\Model\Event\AfterModelSaveEvent;
use ACP3\Core\Model\Event\BeforeModelSaveEvent;
use ACP3\Core\Repository\AbstractRepository;

trait SortingAwareTrait
{
    abstract protected function getRepository(): AbstractRepository;

    abstract protected function getSortHelper(): Sort;

    /**
     * Return the field name of the DB table's primary key.
     */
    abstract protected function getPrimaryKeyField(): string;

    /**
     * Returns the field name which holds the order of the results.
     */
    abstract protected function getSortingField(): string;

    /**
     * Returns an additional field name by which the order of the results should be constrained.
     */
    protected function getSortingConstraint(): string
    {
        return '';
    }

    public function moveUp(int $id): void
    {
        $this->move('up', $id);
    }

    public function moveDown(int $id): void
    {
        $this->move('down', $id);
    }

    private function move(string $action, int $id): void
    {
        $this->dispatchBeforeSaveEvent(
            $this->getRepository(),
            $this->createModelSaveEvent(
                BeforeModelSaveEvent::class,
                $id,
                false,
                true
            )
        );

        if ($action === 'up') {
            $this->getSortHelper()->up(
                $this->getRepository()::TABLE_NAME,
                $this->getPrimaryKeyField(),
                $this->getSortingField(),
                $id,
                $this->getSortingConstraint()
            );
        } else {
            $this->getSortHelper()->down(
                $this->getRepository()::TABLE_NAME,
                $this->getPrimaryKeyField(),
                $this->getSortingField(),
                $id,
                $this->getSortingConstraint()
            );
        }

        $this->dispatchAfterSaveEvent(
            $this->getRepository(),
            $this->createModelSaveEvent(
                AfterModelSaveEvent::class,
                $id,
                false,
                true
            )
        );
    }

    abstract protected function dispatchBeforeSaveEvent(AbstractRepository $repository, AbstractModelSaveEvent $event): void;

    abstract protected function dispatchAfterSaveEvent(AbstractRepository $repository, AbstractModelSaveEvent $event): void;

    /**
     * @param class-string<AbstractModelSaveEvent> $eventType
     * @param array<string, string|int>|int|null   $entryId
     * @param array<string, mixed>|null            $currentData
     * @param array<string, mixed>                 $rawData
     * @param array<string, mixed>                 $filteredData
     */
    abstract protected function createModelSaveEvent(
        string $eventType, array|int|null $entryId,
        bool $isNewEntry,
        bool $hasDataChanges,
        array $filteredData = [],
        array $rawData = [],
        ?array $currentData = null,
    ): AbstractModelSaveEvent;
}
