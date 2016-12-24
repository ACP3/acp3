<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Articles\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\AbstractAdminAction;
use ACP3\Modules\ACP3\Articles;
use ACP3\Modules\ACP3\Menus;

/**
 * Class AbstractFormAction
 * @package ACP3\Modules\ACP3\Articles\Controller\Admin\Index
 */
abstract class AbstractFormAction extends AbstractAdminAction
{
    /**
     * @var Core\Helpers\Forms
     */
    protected $formsHelper;
    /**
     * @var \ACP3\Modules\ACP3\Menus\Helpers\ManageMenuItem
     */
    protected $manageMenuItemHelper;

    /**
     * AbstractFormAction constructor.
     * @param Core\Controller\Context\FrontendContext $context
     * @param Core\Helpers\Forms $formsHelper
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Helpers\Forms $formsHelper
    ) {
        parent::__construct($context);

        $this->formsHelper = $formsHelper;
    }

    /**
     * @param \ACP3\Modules\ACP3\Menus\Helpers\ManageMenuItem $manageMenuItemHelper
     *
     * @return $this
     */
    public function setManageMenuItemHelper(Menus\Helpers\ManageMenuItem $manageMenuItemHelper)
    {
        $this->manageMenuItemHelper = $manageMenuItemHelper;

        return $this;
    }

    /**
     * @param array $formData
     * @param int $articleId
     */
    protected function createOrUpdateMenuItem(array $formData, $articleId)
    {
        if ($this->acl->hasPermission('admin/menus/items/create') === true) {
            $data = [
                'mode' => 4,
                'block_id' => $formData['block_id'],
                'parent_id' => (int)$formData['parent_id'],
                'display' => $formData['display'],
                'title' => $formData['menu_item_title'],
                'target' => 1
            ];

            $this->manageMenuItemHelper->manageMenuItem(
                sprintf(Articles\Helpers::URL_KEY_PATTERN, $articleId),
                isset($formData['create']) === true,
                $data
            );
        }
    }
}
