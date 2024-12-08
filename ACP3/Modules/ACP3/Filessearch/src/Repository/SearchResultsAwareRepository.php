<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Filessearch\Repository;

use ACP3\Core\Database\Connection;
use ACP3\Core\Date;
use ACP3\Core\Repository\PublicationPeriodAwareTrait;
use ACP3\Modules\ACP3\Files\Repository\FilesRepository;
use ACP3\Modules\ACP3\Search\Enum\SearchAreaEnum;
use ACP3\Modules\ACP3\Search\Enum\SortDirectionEnum;

class SearchResultsAwareRepository extends \ACP3\Core\Repository\AbstractRepository implements \ACP3\Modules\ACP3\Search\Repository\SearchResultsAwareRepositoryInterface
{
    use PublicationPeriodAwareTrait;

    public const TABLE_NAME = FilesRepository::TABLE_NAME;

    public function __construct(
        Connection $db,
        private readonly Date $date,
    ) {
        parent::__construct($db);
    }

    /**
     * @{@inheritDoc}
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAllSearchResults(SearchAreaEnum $area, string $searchTerm, SortDirectionEnum $sortDirection): array
    {
        return $this->db->fetchAll(
            "SELECT `id`, `title`, `text` FROM {$this->getTableName()} WHERE MATCH ({$this->mapSearchAreasToFields($area)}) AGAINST ({$this->db->getConnection()->quote($searchTerm)} IN BOOLEAN MODE) AND {$this->getPublicationPeriod()} ORDER BY `start` $sortDirection->value, `end` $sortDirection->value, `id` $sortDirection->value",
            ['time' => $this->date->getCurrentDateTime()]
        );
    }

    private function mapSearchAreasToFields(SearchAreaEnum $area): string
    {
        return match ($area) {
            SearchAreaEnum::TITLE => 'title, file',
            SearchAreaEnum::CONTENT => 'text',
            default => 'title, file, text',
        };
    }
}
