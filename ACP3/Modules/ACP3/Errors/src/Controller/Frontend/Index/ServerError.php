<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Errors\Controller\Frontend\Index;

use ACP3\Core;
use Symfony\Component\HttpFoundation\Response;

class ServerError extends Core\Controller\AbstractFrontendAction
{
    public function execute()
    {
        $this->breadcrumb->append(
            $this->translator->t('errors', 'frontend_index_server_error'),
            $this->request->getQuery()
        );

        $this->response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}