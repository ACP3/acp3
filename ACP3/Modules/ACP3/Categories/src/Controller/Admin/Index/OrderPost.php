<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Http\RedirectResponse;
use ACP3\Modules\ACP3\Categories\Model\CategoriesModel;
use ACP3\Modules\ACP3\Categories\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;

class OrderPost extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly RedirectResponse $redirectResponse,
        private readonly CategoryRepository $categoriesRepository,
        private readonly CategoriesModel $categoriesModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id, string $action): Response
    {
        if (($action === 'up' || $action === 'down') && $this->categoriesRepository->resultExists($id) === true) {
            if ($action === 'up') {
                $this->categoriesModel->moveUp($id);
            } else {
                $this->categoriesModel->moveDown($id);
            }

            return $this->redirectResponse->temporary('acp/categories');
        }

        throw new ResultNotExistsException();
    }
}
