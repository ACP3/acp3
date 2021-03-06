<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Seo;

class Edit extends Core\Controller\AbstractWidgetAction implements Core\Controller\InvokableActionInterface
{
    /**
     * @var Seo\Model\SeoModel
     */
    private $seoModel;
    /**
     * @var \ACP3\Modules\ACP3\Seo\ViewProviders\AdminSeoEditViewProvider
     */
    private $adminSeoEditViewProvider;

    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Seo\Model\SeoModel $seoModel,
        Seo\ViewProviders\AdminSeoEditViewProvider $adminSeoEditViewProvider
    ) {
        parent::__construct($context);

        $this->seoModel = $seoModel;
        $this->adminSeoEditViewProvider = $adminSeoEditViewProvider;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $seo = $this->seoModel->getOneById($id);

        if (empty($seo) === false) {
            return ($this->adminSeoEditViewProvider)($seo);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
