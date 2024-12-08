<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Model;

use ACP3\Core\Model\AbstractModel;
use ACP3\Core\Model\DataProcessor;
use ACP3\Core\Model\DataProcessor\ColumnType\BooleanColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\DateTimeColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\IntegerColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\TextColumnType;
use ACP3\Core\Model\DataProcessor\ColumnType\TextWysiwygColumnType;
use ACP3\Core\Model\UpdatedAtAwareModelInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Newsletter\Installer\Schema;
use ACP3\Modules\ACP3\Newsletter\Repository\NewsletterRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class NewsletterModel extends AbstractModel implements UpdatedAtAwareModelInterface
{
    public const EVENT_PREFIX = Schema::MODULE_NAME;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        DataProcessor $dataProcessor,
        protected SettingsInterface $config,
        NewsletterRepository $newsletterRepository,
    ) {
        parent::__construct($eventDispatcher, $dataProcessor, $newsletterRepository);
    }

    public function save(array $rawData, ?int $entryId = null): int
    {
        if ($entryId === null) {
            $settings = $this->config->getSettings(Schema::MODULE_NAME);
            $rawData['html'] = $settings['html'];
            $rawData['status'] = 0;
        }

        $rawData['updated_at'] = 'now';

        return parent::save($rawData, $entryId);
    }

    protected function getAllowedColumns(): array
    {
        return [
            'date' => DateTimeColumnType::class,
            'updated_at' => DateTimeColumnType::class,
            'title' => TextColumnType::class,
            'text' => TextWysiwygColumnType::class,
            'user_id' => IntegerColumnType::class,
            'html' => BooleanColumnType::class,
            'status' => IntegerColumnType::class,
        ];
    }
}
