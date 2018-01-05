<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Index extends AbstractAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Pagination
     */
    protected $pagination;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Model\Repository\GalleryRepository
     */
    protected $galleryRepository;

    /**
     * Index constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext      $context
     * @param \ACP3\Core\Date                                    $date
     * @param \ACP3\Core\Pagination                              $pagination
     * @param \ACP3\Modules\ACP3\Gallery\Model\Repository\GalleryRepository $galleryRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        Core\Pagination $pagination,
        Gallery\Model\Repository\GalleryRepository $galleryRepository
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->pagination = $pagination;
        $this->galleryRepository = $galleryRepository;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        $resultsPerPage = $this->resultsPerPage->getResultsPerPage(Gallery\Installer\Schema::MODULE_NAME);
        $time = $this->date->getCurrentDateTime();
        $this->pagination
            ->setResultsPerPage($resultsPerPage)
            ->setTotalResults($this->galleryRepository->countAll($time));

        return [
            'galleries' => $this->galleryRepository->getAll(
                $time,
                $this->pagination->getResultsStartOffset(),
                $resultsPerPage
            ),
            'dateformat' => $this->settings['dateformat'],
            'pagination' => $this->pagination->render()
        ];
    }
}
