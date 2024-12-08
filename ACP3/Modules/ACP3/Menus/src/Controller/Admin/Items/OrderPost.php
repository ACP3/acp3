<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Items;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Controller\Exception\ResultNotExistsException;
use ACP3\Core\Http\RedirectResponse;
use ACP3\Modules\ACP3\Menus\Model\MenuItemsModel;
use ACP3\Modules\ACP3\Menus\Repository\MenuItemRepository;
use Symfony\Component\HttpFoundation\Response;

class OrderPost extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly RedirectResponse $redirectResponse,
        private readonly MenuItemRepository $menuItemRepository,
        private readonly MenuItemsModel $menuItemsModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id, string $action): Response
    {
        if (($action === 'up' || $action === 'down') && $this->menuItemRepository->menuItemExists($id) === true) {
            if ($action === 'up') {
                $this->menuItemsModel->moveUp($id);
            } else {
                $this->menuItemsModel->moveDown($id);
            }

            return $this->redirectResponse->temporary('acp/menus');
        }

        throw new ResultNotExistsException();
    }
}
