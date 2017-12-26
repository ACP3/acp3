<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Frontend\Archive;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;
use ACP3\Modules\ACP3\System\Installer\Schema;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Newsletter\Controller\Frontend\Archive
 */
class Index extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var Core\Pagination
     */
    protected $pagination;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterRepository
     */
    protected $newsletterRepository;

    /**
     * @param \ACP3\Core\Controller\Context\FrontendContext            $context
     * @param Core\Pagination                                          $pagination
     * @param \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterRepository $newsletterRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Pagination $pagination,
        Newsletter\Model\Repository\NewsletterRepository $newsletterRepository
    ) {
        parent::__construct($context);

        $this->pagination = $pagination;
        $this->newsletterRepository = $newsletterRepository;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        $resultsPerPage = $this->resultsPerPage->getResultsPerPage(Newsletter\Installer\Schema::MODULE_NAME);
        $this->pagination
            ->setResultsPerPage($resultsPerPage)
            ->setTotalResults($this->newsletterRepository->countAll(1));

        return [
            'newsletters' => $this->newsletterRepository->getAll(
                Newsletter\Helper\AccountStatus::ACCOUNT_STATUS_CONFIRMED,
                $this->pagination->getResultsStartOffset(),
                $resultsPerPage
            ),
            'pagination' => $this->pagination->render()
        ];
    }
}
