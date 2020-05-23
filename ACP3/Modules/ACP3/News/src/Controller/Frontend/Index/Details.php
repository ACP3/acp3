<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\News;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Details extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var Core\Date
     */
    private $date;
    /**
     * @var \ACP3\Modules\ACP3\News\Model\Repository\NewsRepository
     */
    private $newsRepository;
    /**
     * @var \ACP3\Modules\ACP3\News\ViewProviders\NewsDetailsViewProvider
     */
    private $newsDetailsViewProvider;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        News\Model\Repository\NewsRepository $newsRepository,
        News\ViewProviders\NewsDetailsViewProvider $newsDetailsViewProvider
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->newsRepository = $newsRepository;
        $this->newsDetailsViewProvider = $newsDetailsViewProvider;
    }

    /**
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(int $id): array
    {
        if ($this->newsRepository->resultExists($id, $this->date->getCurrentDateTime()) == 1) {
            $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return ($this->newsDetailsViewProvider)($id);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
