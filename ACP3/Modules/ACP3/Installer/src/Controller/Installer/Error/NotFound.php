<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Controller\Installer\Error;

use ACP3\Core\Controller\AbstractWidgetAction;
use Symfony\Component\HttpFoundation\Response;

class NotFound extends AbstractWidgetAction
{
    public function __invoke(): Response
    {
        return new Response(
            $this->view->fetchTemplate('Installer/Installer/error.not_found.tpl'),
            Response::HTTP_NOT_FOUND
        );
    }
}
