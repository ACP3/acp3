<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Errors\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Breadcrumb\Steps;
use ACP3\Core\Controller\Context\Context;
use Symfony\Component\HttpFoundation\Response;

class AccessForbidden extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly Steps $breadcrumb,
    ) {
        parent::__construct($context);
    }

    public function __invoke(): Response
    {
        $this->breadcrumb->append(
            $this->translator->t('errors', 'frontend_index_access_forbidden'),
            $this->request->getQuery()
        );

        return new Response(
            $this->view->fetchTemplate('Errors/Frontend/index.access_forbidden.tpl'),
            Response::HTTP_FORBIDDEN
        );
    }
}
