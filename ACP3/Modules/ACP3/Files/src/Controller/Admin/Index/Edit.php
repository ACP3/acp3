<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Files;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Files\Model\FilesModel $filesModel,
        private readonly Files\ViewProviders\AdminFileEditViewProvider $adminFileEditViewProvider,
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
        $file = $this->filesModel->getOneById($id);

        if (empty($file) === false) {
            return ($this->adminFileEditViewProvider)($file);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
