<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Contact\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Contact\Model\ContactsModel;
use Symfony\Component\HttpFoundation\Response;

class Delete extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        private FormAction $actionHelper,
        private ContactsModel $contactsModel
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     */
    public function __invoke(?string $action): array|Response
    {
        return $this->actionHelper->handleDeleteAction(
            $action,
            fn (array $items) => $this->contactsModel->delete($items)
        );
    }
}
