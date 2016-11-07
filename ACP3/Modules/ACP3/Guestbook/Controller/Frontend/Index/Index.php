<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers. See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Guestbook;
use ACP3\Modules\ACP3\Newsletter;
use ACP3\Modules\ACP3\System\Installer\Schema;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Guestbook\Controller\Frontend\Index
 */
class Index extends AbstractAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Core\Pagination
     */
    protected $pagination;
    /**
     * @var \ACP3\Modules\ACP3\Guestbook\Model\Repository\GuestbookRepository
     */
    protected $guestbookRepository;

    /**
     * Index constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Core\Pagination $pagination
     * @param \ACP3\Modules\ACP3\Guestbook\Model\Repository\GuestbookRepository $guestbookRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Pagination $pagination,
        Guestbook\Model\Repository\GuestbookRepository $guestbookRepository
    ) {
        parent::__construct($context);

        $this->pagination = $pagination;
        $this->guestbookRepository = $guestbookRepository;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        $resultsPerPage = $this->resultsPerPage->getResultsPerPage(Guestbook\Installer\Schema::MODULE_NAME);
        $this->pagination
            ->setResultsPerPage($resultsPerPage)
            ->setTotalResults($this->guestbookRepository->countAll($this->guestbookSettings['notify']));

        $guestbook = $this->guestbookRepository->getAll(
            $this->guestbookSettings['notify'],
            $this->pagination->getResultsStartOffset(),
            $resultsPerPage
        );
        $cGuestbook = count($guestbook);

        for ($i = 0; $i < $cGuestbook; ++$i) {
            if ($this->guestbookSettings['emoticons'] == 1 && $this->emoticonsHelpers) {
                $guestbook[$i]['message'] = $this->emoticonsHelpers->emoticonsReplace($guestbook[$i]['message']);
            }
        }

        return [
            'guestbook' => $guestbook,
            'overlay' => $this->guestbookSettings['overlay'],
            'pagination' => $this->pagination->render(),
            'dateformat' => $this->guestbookSettings['dateformat']
        ];
    }
}
