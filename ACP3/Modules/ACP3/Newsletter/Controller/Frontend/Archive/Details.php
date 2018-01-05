<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Frontend\Archive;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Details extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterRepository
     */
    protected $newsletterRepository;

    /**
     * Details constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext            $context
     * @param \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterRepository $newsletterRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Newsletter\Model\Repository\NewsletterRepository $newsletterRepository
    ) {
        parent::__construct($context);

        $this->newsletterRepository = $newsletterRepository;
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        $newsletter = $this->newsletterRepository->getOneByIdAndStatus($id, 1);

        if (!empty($newsletter)) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            $this->breadcrumb
                ->append($this->translator->t('newsletter', 'index'), 'newsletter')
                ->append($this->translator->t('newsletter', 'frontend_archive_index'), 'newsletter/archive')
                ->append($newsletter['title']);
            $this->title->setPageTitle($newsletter['title']);

            return [
                'newsletter' => $newsletter
            ];
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
