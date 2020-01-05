<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;

class Latest extends Core\Controller\AbstractWidgetAction
{
    /**
     * @var Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\News\Model\Repository\NewsRepository
     */
    protected $newsRepository;

    /**
     * @param \ACP3\Core\Controller\Context\WidgetContext             $context
     * @param \ACP3\Core\Date                                         $date
     * @param \ACP3\Modules\ACP3\News\Model\Repository\NewsRepository $newsRepository
     */
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Core\Date $date,
        News\Model\Repository\NewsRepository $newsRepository
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->newsRepository = $newsRepository;
    }

    /**
     * @param int $categoryId
     *
     * @return array
     */
    public function execute($categoryId = 0)
    {
        $settings = $this->config->getSettings(News\Installer\Schema::MODULE_NAME);

        if (!empty($categoryId)) {
            $news = $this->newsRepository->getLatestByCategoryId((int) $categoryId, $this->date->getCurrentDateTime());
        } else {
            $news = $this->newsRepository->getLatest($this->date->getCurrentDateTime());
        }

        return [
            'sidebar_news_latest' => $news,
            'dateformat' => $settings['dateformat'],
        ];
    }
}