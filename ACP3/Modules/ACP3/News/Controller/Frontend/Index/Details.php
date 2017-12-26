<?php
/**
 * Copyright (c) by the ACP3 Developers. See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;
use ACP3\Modules\ACP3\System\Installer\Schema;

/**
 * Class Details
 * @package ACP3\Modules\ACP3\News\Controller\Frontend\Index
 */
class Details extends AbstractAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\News\Model\Repository\NewsRepository
     */
    protected $newsRepository;
    /**
     * @var News\Cache
     */
    protected $newsCache;

    /**
     * Details constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Core\Date                               $date
     * @param \ACP3\Modules\ACP3\News\Model\Repository\NewsRepository  $newsRepository
     * @param \ACP3\Modules\ACP3\News\Cache                 $newsCache
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        News\Model\Repository\NewsRepository $newsRepository,
        News\Cache $newsCache
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->newsRepository = $newsRepository;
        $this->newsCache = $newsCache;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        if ($this->newsRepository->resultExists($id, $this->date->getCurrentDateTime()) == 1) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            $news = $this->newsCache->getCache($id);

            $this->breadcrumb->append($this->translator->t('news', 'news'), 'news');

            if ($this->newsSettings['category_in_breadcrumb'] == 1) {
                $this->breadcrumb->append($news['category_title'], 'news/index/index/cat_' . $news['category_id']);
            }
            $this->breadcrumb->append($news['title']);
            $this->title->setPageTitle($news['title']);

            $news['text'] = $this->view->fetchStringAsTemplate($news['text']);
            $news['target'] = $news['target'] == 2 ? ' target="_blank"' : '';

            return [
                'news' => $news,
                'dateformat' => $this->newsSettings['dateformat'],
                'comments_allowed' => $this->commentsActive === true && $news['comments'] == 1
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
