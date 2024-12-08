<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories;
use ACP3\Modules\ACP3\Files\ViewProviders\FilesByCategoryIdViewProvider;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Files extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Categories\Repository\CategoryRepository $categoryRepository,
        private readonly FilesByCategoryIdViewProvider $filesByCategoryIdViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws Core\Controller\Exception\ResultNotExistsException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $cat): Response
    {
        if ($this->categoryRepository->resultExists($cat) === true) {
            $response = $this->renderTemplate(null, ($this->filesByCategoryIdViewProvider)($cat));
            $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

            return $response;
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
