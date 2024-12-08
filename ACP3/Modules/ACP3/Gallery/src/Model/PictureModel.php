<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Model;

use ACP3\Core\Helpers\Sort;
use ACP3\Core\Model\AbstractModel;
use ACP3\Core\Model\DataProcessor;
use ACP3\Core\Model\DataProcessor\ColumnType\IntegerColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\RawColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\TextColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\TextWysiwygColumnType;
use ACP3\Core\Model\SortingAwareInterface;
use ACP3\Core\Model\SortingAwareTrait;
use ACP3\Modules\ACP3\Gallery\Installer\Schema;
use ACP3\Modules\ACP3\Gallery\Repository\PictureRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @property PictureRepository $repository
 */
class PictureModel extends AbstractModel implements SortingAwareInterface
{
    use SortingAwareTrait;

    public const EVENT_PREFIX = Schema::MODULE_NAME;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DataProcessor $dataProcessor,
        PictureRepository $pictureRepository,
        private readonly Sort $sortHelper,
    ) {
        parent::__construct($eventDispatcher, $dataProcessor, $pictureRepository);
    }

    public function save(array $rawData, ?int $entryId = null): int
    {
        if ($entryId === null) {
            $rawData[$this->getSortingField()] = $this->getPictureSortIndex($rawData['gallery_id']);
        } else {
            $picture = $this->repository->getOneById($entryId);

            if ((int) $rawData['gallery_id'] !== (int) $picture['gallery_id']) {
                $rawData[$this->getSortingField()] = $this->getPictureSortIndex($rawData['gallery_id']);
            }
        }

        return parent::save($rawData, $entryId);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getPictureSortIndex(int $galleryId): int
    {
        $picNum = $this->repository->getLastPictureByGalleryId($galleryId);

        return $picNum + 1;
    }

    protected function getAllowedColumns(): array
    {
        return [
            'gallery_id' => IntegerColumnType::class,
            'title' => TextColumnType::class,
            'description' => TextWysiwygColumnType::class,
            'file' => RawColumnType::class,
            'pic' => IntegerColumnType::class,
        ];
    }

    protected function getSortHelper(): Sort
    {
        return $this->sortHelper;
    }

    protected function getPrimaryKeyField(): string
    {
        return 'id';
    }

    protected function getSortingField(): string
    {
        return 'pic';
    }

    protected function getSortingConstraint(): string
    {
        return 'gallery_id';
    }
}
