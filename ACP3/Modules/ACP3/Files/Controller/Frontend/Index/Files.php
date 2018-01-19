<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories;
use ACP3\Modules\ACP3\Files as FilesModule;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Files extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\Files\Model\Repository\FilesRepository
     */
    protected $filesRepository;
    /**
     * @var \ACP3\Modules\ACP3\Categories\Model\Repository\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Files constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext                     $context
     * @param \ACP3\Core\Date                                                   $date
     * @param \ACP3\Modules\ACP3\Files\Model\Repository\FilesRepository         $filesRepository
     * @param \ACP3\Modules\ACP3\Categories\Model\Repository\CategoryRepository $categoryRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        FilesModule\Model\Repository\FilesRepository $filesRepository,
        Categories\Model\Repository\CategoryRepository $categoryRepository
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->filesRepository = $filesRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param int $cat
     *
     * @return array
     *
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($cat)
    {
        if ($this->categoryRepository->resultExists($cat) === true) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            $category = $this->categoryRepository->getOneById($cat);

            $this->breadcrumb
                ->append($this->translator->t('files', 'files'), 'files')
                ->append($category['title']);

            $settings = $this->config->getSettings(FilesModule\Installer\Schema::MODULE_NAME);

            return [
                'dateformat' => $settings['dateformat'],
                'files' => $this->filesRepository->getAllByCategoryId($cat, $this->date->getCurrentDateTime()),
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
