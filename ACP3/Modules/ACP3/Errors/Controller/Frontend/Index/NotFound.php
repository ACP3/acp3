<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Errors\Controller\Frontend\Index;

use ACP3\Core;
use Symfony\Component\HttpFoundation\Response;

class NotFound extends Core\Controller\AbstractFrontendAction
{
    public function execute()
    {
        $this->breadcrumb->append($this->translator->t('errors', 'frontend_index_not_found'));

        $this->response->setStatusCode(Response::HTTP_NOT_FOUND);
    }
}
