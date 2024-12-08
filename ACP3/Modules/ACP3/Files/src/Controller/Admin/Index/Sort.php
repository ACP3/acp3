<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Files\Model\FilesModel;
use ACP3\Modules\ACP3\Files\Repository\FilesRepository;
use Symfony\Component\HttpFoundation\Response;

class Sort extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly Core\Http\RedirectResponse $redirectResponse,
        private readonly FilesRepository $filesRepository,
        private readonly FilesModel $filesModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id, string $action): Response
    {
        if (($action === 'up' || $action === 'down') && $this->filesRepository->resultExists($id) === true) {
            if ($action === 'up') {
                $this->filesModel->moveUp($id);
            } else {
                $this->filesModel->moveDown($id);
            }

            return $this->redirectResponse->temporary('acp/files');
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
