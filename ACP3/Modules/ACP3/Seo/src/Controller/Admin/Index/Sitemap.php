<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Helpers\RedirectMessages;
use ACP3\Modules\ACP3\Seo\Installer\Schema;
use ACP3\Modules\ACP3\Seo\Model\SitemapGenerationModel;
use Symfony\Component\HttpFoundation\Response;

class Sitemap extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly RedirectMessages $redirectMessages,
        private readonly SitemapGenerationModel $sitemapGenerationModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \ACP3\Modules\ACP3\Seo\Exception\SitemapGenerationException
     */
    public function __invoke(): Response
    {
        $result = false;
        $phrase = 'sitemap_error';
        if ($this->config->getSettings(Schema::MODULE_NAME)['sitemap_is_enabled'] == 1) {
            $result = $this->sitemapGenerationModel->save();
            $phrase = $result === true ? 'sitemap_success' : 'sitemap_error';
        }

        return $this->redirectMessages->setMessage($result, $this->translator->t('seo', $phrase));
    }
}
