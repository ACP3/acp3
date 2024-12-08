<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Model;

use ACP3\Core\Model\AbstractModel;
use ACP3\Core\Model\DataProcessor;
use ACP3\Core\Model\DataProcessor\ColumnType\BooleanColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\DateTimeColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\IntegerColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\TextColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\TextWysiwygColumnType;
use ACP3\Core\Model\DuplicationAwareTrait;
use ACP3\Core\Model\UpdatedAtAwareModelInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\News\Installer\Schema;
use ACP3\Modules\ACP3\News\Repository\NewsRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NewsModel extends AbstractModel implements UpdatedAtAwareModelInterface
{
    use DuplicationAwareTrait;

    public const EVENT_PREFIX = Schema::MODULE_NAME;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DataProcessor $dataProcessor,
        private readonly SettingsInterface $config,
        NewsRepository $newsRepository,
    ) {
        parent::__construct($eventDispatcher, $dataProcessor, $newsRepository);
    }

    public function save(array $rawData, ?int $entryId = null): int
    {
        $settings = $this->config->getSettings(Schema::MODULE_NAME);

        $rawData = array_merge($rawData, [
            'updated_at' => 'now',
            'readmore' => $this->useReadMore($rawData, $settings),
            'category_id' => $rawData['cat'] ?? $rawData['category_id'],
        ]);

        return parent::save($rawData, $entryId);
    }

    /**
     * @param array<string, mixed> $formData
     * @param array<string, mixed> $settings
     */
    protected function useReadMore(array $formData, array $settings): int
    {
        return $settings['readmore'] == 1 && isset($formData['readmore']) ? 1 : 0;
    }

    protected function getAllowedColumns(): array
    {
        return [
            'active' => BooleanColumnType::class,
            'start' => DateTimeColumnType::class,
            'end' => DateTimeColumnType::class,
            'updated_at' => DateTimeColumnType::class,
            'title' => TextColumnType::class,
            'subtitle' => TextColumnType::class,
            'text' => TextWysiwygColumnType::class,
            'readmore' => IntegerColumnType::class,
            'category_id' => IntegerColumnType::class,
            'uri' => TextWysiwygColumnType::class,
            'target' => IntegerColumnType::class,
            'link_title' => TextColumnType::class,
            'user_id' => IntegerColumnType::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaultDataForDuplication(): array
    {
        return [
            'active' => 0,
            'start' => 'now',
            'end' => 'now',
        ];
    }
}
