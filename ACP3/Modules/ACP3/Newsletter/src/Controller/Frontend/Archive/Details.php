<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
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
    private $newsletterRepository;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\ViewProviders\NewsletterDetailsViewProvider
     */
    private $newsletterDetailsViewProvider;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Newsletter\Model\Repository\NewsletterRepository $newsletterRepository,
        Newsletter\ViewProviders\NewsletterDetailsViewProvider $newsletterDetailsViewProvider
    ) {
        parent::__construct($context);

        $this->newsletterRepository = $newsletterRepository;
        $this->newsletterDetailsViewProvider = $newsletterDetailsViewProvider;
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $id): array
    {
        $newsletter = $this->newsletterRepository->getOneByIdAndStatus($id, 1);

        if (!empty($newsletter)) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return ($this->newsletterDetailsViewProvider)($newsletter);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}