<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories\Model\Repository\CategoryRepository;
use ACP3\Modules\ACP3\News;
use ACP3\Modules\ACP3\System\Installer\Schema;

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
     * @var \ACP3\Modules\ACP3\Categories\Model\Repository\CategoryRepository
     */
    private $categoryRepository;

    /**
     * Details constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext                     $context
     * @param \ACP3\Core\Date                                                   $date
     * @param \ACP3\Modules\ACP3\News\Model\Repository\NewsRepository           $newsRepository
     * @param \ACP3\Modules\ACP3\News\Cache                                     $newsCache
     * @param \ACP3\Modules\ACP3\Categories\Model\Repository\CategoryRepository $categoryRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        News\Model\Repository\NewsRepository $newsRepository,
        News\Cache $newsCache,
        CategoryRepository $categoryRepository
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->newsRepository = $newsRepository;
        $this->newsCache = $newsCache;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param int $id
     *
     * @return array
     *
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute($id)
    {
        if ($this->newsRepository->resultExists($id, $this->date->getCurrentDateTime()) == 1) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            $news = $this->newsCache->getCache($id);

            $this->addBreadcrumbSteps(
                $news,
                $news['category_id'],
                $this->newsSettings['category_in_breadcrumb'] == 1
            );

            $news['text'] = $this->view->fetchStringAsTemplate($news['text']);
            $news['target'] = $news['target'] == 2 ? ' target="_blank"' : '';

            return [
                'news' => $news,
                'dateformat' => $this->newsSettings['dateformat'],
                'comments_allowed' => $this->commentsActive === true && $news['comments'] == 1,
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }

    /**
     * @param array $news
     * @param int   $categoryId
     * @param bool  $showCategoriesInBreadcrumb
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function addBreadcrumbSteps(array $news, int $categoryId, bool $showCategoriesInBreadcrumb): void
    {
        $this->breadcrumb->append($this->translator->t('news', 'news'), 'news');
        if ($showCategoriesInBreadcrumb === true) {
            foreach ($this->categoryRepository->fetchNodeWithParents($categoryId) as $category) {
                $this->breadcrumb->append($category['title'], 'news/index/index/cat_' . $category['id']);
            }
        }
        $this->breadcrumb->append(
            $news['title'],
            $this->request->getQuery()
        );
        $this->title->setPageTitle($news['title']);
    }
}
