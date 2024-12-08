<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Share\Model\ShareModel;
use ACP3\Modules\ACP3\Share\ViewProviders\AdminShareEditViewProvider;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ShareModel $shareModel,
        private readonly AdminShareEditViewProvider $adminShareEditViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $sharingInfo = $this->shareModel->getOneById($id);

        if (empty($sharingInfo) === false) {
            return ($this->adminShareEditViewProvider)($sharingInfo);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
