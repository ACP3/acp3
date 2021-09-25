<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Gallery\ViewProviders\GalleryListWidgetViewProvider
     */
    private $galleryListWidgetViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Gallery\ViewProviders\GalleryListWidgetViewProvider $galleryListWidgetViewProvider
    ) {
        parent::__construct($context);

        $this->galleryListWidgetViewProvider = $galleryListWidgetViewProvider;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): Response
    {
        $response = $this->renderTemplate(null, ($this->galleryListWidgetViewProvider)());
        $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        return $response;
    }
}
