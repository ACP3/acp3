<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Model;

use ACP3\Core\Model\DataProcessor\ColumnType\ColumnTypeStrategyInterface;
use ACP3\Core\Model\Event\AbstractModelSaveEvent;
use ACP3\Core\Model\Event\AfterModelDeleteEvent;
use ACP3\Core\Model\Event\AfterModelSaveEvent;
use ACP3\Core\Model\Event\BeforeModelDeleteEvent;
use ACP3\Core\Model\Event\BeforeModelSaveEvent;
use ACP3\Core\Model\Event\ModelSavePrepareDataEvent;
use ACP3\Core\Repository\AbstractRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractModel
{
    public const EVENT_PREFIX = '';

    public function __construct(protected EventDispatcherInterface $eventDispatcher, private readonly DataProcessor $dataProcessor, protected AbstractRepository $repository)
    {
    }

    protected function getDataProcessor(): DataProcessor
    {
        return $this->dataProcessor;
    }

    /**
     * @param array<string, mixed> $rawData
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function save(array $rawData, ?int $entryId = null): int
    {
        $currentData = $this->loadCurrentData($entryId);
        $filteredNewData = $this->prepareData($rawData, $currentData);

        $isNewEntry = $entryId === null;
        $hasDataChanges = $this->hasDataChanges($filteredNewData, $currentData);
        $event = new BeforeModelSaveEvent(static::EVENT_PREFIX, $filteredNewData, $rawData, $entryId, $isNewEntry, $hasDataChanges, $this->repository::TABLE_NAME, $currentData);

        $this->dispatchBeforeSaveEvent($this->repository, $event);

        $entryId = $this->doUpsert($entryId, $filteredNewData);

        $event = new AfterModelSaveEvent(static::EVENT_PREFIX, $filteredNewData, $rawData, $entryId, $isNewEntry, $hasDataChanges, $this->repository::TABLE_NAME, $currentData);
        $this->dispatchAfterSaveEvent(
            $this->repository,
            $event
        );

        return $entryId;
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws \Doctrine\DBAL\Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function loadCurrentData(?int $entryId): ?array
    {
        if ($entryId === null) {
            return null;
        }

        $result = $this->getOneById($entryId);

        return $this->dataProcessor->escape($result, $this->getAllowedColumns());
    }

    /**
     * @param array<string, mixed>      $filteredData
     * @param array<string, mixed>|null $currentData
     */
    protected function hasDataChanges(array $filteredData, ?array $currentData): bool
    {
        if ($currentData === null) {
            return true;
        }

        if ($this instanceof UpdatedAtAwareModelInterface) {
            unset($currentData['updated_at']);
        }

        foreach ($currentData as $column => $value) {
            if (\array_key_exists($column, $filteredData) && $filteredData[$column] !== $value) {
                return true;
            }
        }

        return false;
    }

    protected function dispatchBeforeSaveEvent(
        AbstractRepository $repository,
        AbstractModelSaveEvent $event,
    ): void {
        $this->dispatchEvent($event);
        $this->dispatchEvent(
            $event,
            'core.model.before_save'
        );
        $this->dispatchEvent(
            $event,
            static::EVENT_PREFIX . '.model.' . $repository::TABLE_NAME . '.before_save'
        );
    }

    protected function dispatchEvent(
        object $event,
        ?string $eventName = null): void
    {
        $this->eventDispatcher->dispatch(
            $event,
            $eventName
        );
    }

    /**
     * @param class-string<AbstractModelSaveEvent> $eventType
     * @param array<string, string|int>|int|null   $entryId
     * @param array<string, mixed>|null            $currentData
     * @param array<string, mixed>                 $rawData
     * @param array<string, mixed>                 $filteredData
     */
    protected function createModelSaveEvent(
        string $eventType,
        array|int|null $entryId,
        bool $isNewEntry,
        bool $hasDataChanges,
        array $filteredData = [],
        array $rawData = [],
        ?array $currentData = null,
    ): AbstractModelSaveEvent {
        return new $eventType(
            static::EVENT_PREFIX,
            $filteredData,
            $rawData,
            $entryId,
            $isNewEntry,
            $hasDataChanges,
            $this->repository::TABLE_NAME,
            $currentData
        );
    }

    /**
     * @param array<string, mixed>      $rawData
     * @param array<string, mixed>|null $currentData
     *
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function prepareData(array $rawData, ?array $currentData): array
    {
        if ($currentData !== null) {
            $rawData = [...$currentData, ...$rawData];
        }

        $modelSavePrepareDataEvent = new ModelSavePrepareDataEvent($rawData, $currentData, $this->getAllowedColumns());

        $this->dispatchEvent(
            $modelSavePrepareDataEvent,
            static::EVENT_PREFIX . '.model.' . $this->repository::TABLE_NAME . '.prepare_data'
        );

        return $this->dataProcessor->escape(
            $modelSavePrepareDataEvent->getRawData(),
            $modelSavePrepareDataEvent->getAllowedColumns()
        );
    }

    /**
     * @return array<string, class-string<ColumnTypeStrategyInterface>>
     */
    abstract protected function getAllowedColumns(): array;

    protected function dispatchAfterSaveEvent(
        AbstractRepository $repository,
        AbstractModelSaveEvent $event,
    ): void {
        $this->dispatchEvent($event);
        $this->dispatchEvent(
            $event,
            'core.model.after_save'
        );
        $this->dispatchEvent(
            $event,
            static::EVENT_PREFIX . '.model.' . $repository::TABLE_NAME . '.after_save'
        );
    }

    /**
     * @param array<string, mixed> $filteredNewData
     *
     * @return int The ID of the (possibly) created result set
     *
     * @throws \Doctrine\DBAL\Exception
     */
    protected function doUpsert(?int $entryId, array $filteredNewData): int
    {
        if ($entryId === null) {
            return $this->repository->insert($filteredNewData);
        }

        $this->repository->update($filteredNewData, $entryId);

        return $entryId;
    }

    /**
     * @param int[]|int $entryId
     *
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
            $affectedRows += $this->repository->delete($item);
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
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getOneById(int $entryId): array
    {
        return $this->repository->getOneById($entryId);
    }

    protected function getRepository(): AbstractRepository
    {
        return $this->repository;
    }
}
