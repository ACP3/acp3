<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Categories\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Categories\Model\CategoriesModel $categoriesModel,
        private readonly Categories\ViewProviders\AdminCategoryEditViewProvider $adminCategoryEditViewProvider,
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
        $category = $this->categoriesModel->getOneById($id);

        if (empty($category) === false) {
            return ($this->adminCategoryEditViewProvider)($category);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
