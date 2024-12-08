<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Widget\Index;

use ACP3\Core\Cache\CacheResponseTrait;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Gallery\ViewProviders\GalleryPictureListWidgetViewProvider;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\HttpFoundation\Response;

class Pictures extends AbstractWidgetAction
{
    use CacheResponseTrait;

    public function __construct(
        Context $context,
        private readonly GalleryPictureListWidgetViewProvider $galleryPictureListWidgetViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \ACP3\Core\Picture\Exception\PictureGenerateException
     */
    public function __invoke(int $id, string $template = ''): Response
    {
        $response = $this->renderTemplate(urldecode($template), ($this->galleryPictureListWidgetViewProvider)($id));
        $this->setCacheResponseCacheable($response, $this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        return $response;
    }
}
