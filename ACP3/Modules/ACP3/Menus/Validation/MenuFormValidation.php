<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Validation;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus\Validation\ValidationRules\MenuAlreadyExistsValidationRule;
use ACP3\Modules\ACP3\Menus\Validation\ValidationRules\MenuNameValidationRule;

class MenuFormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @var int
     */
    protected $menuId = 0;

    /**
     * @param int $menuId
     *
     * @return $this
     */
    public function setMenuId($menuId)
    {
        $this->menuId = (int)$menuId;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                MenuNameValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'index_name',
                    'message' => $this->translator->t('menus', 'type_in_index_name'),
                ]
            )
            ->addConstraint(
                MenuAlreadyExistsValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'index_name',
                    'message' => $this->translator->t('menus', 'index_name_unique'),
                    'extra' => [
                        'menu_id' => $this->menuId,
                    ],
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'title',
                    'message' => $this->translator->t('menus', 'menu_bar_title_to_short'),
                ]
            );

        $this->validator->validate();
    }
}
