<?php
namespace ACP3\Modules\ACP3\Menus\Validation;

use ACP3\Core;
use ACP3\Modules\ACP3\Menus\Validation\ValidationRules\MenuAlreadyExistsValidationRule;
use ACP3\Modules\ACP3\Menus\Validation\ValidationRules\MenuNameValidationRule;

class MenuFormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @var int
     */
    protected $menuId;

    /**
     * @param int|null $menuId
     *
     * @return $this
     */
    public function setMenuId(?int $menuId)
    {
        $this->menuId = $menuId;

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
                    'message' => $this->translator->t('menus', 'type_in_index_name')
                ]
            )
            ->addConstraint(
                MenuAlreadyExistsValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'index_name',
                    'message' => $this->translator->t('menus', 'index_name_unique'),
                    'extra' => [
                        'menu_id' => $this->menuId
                    ]
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'title',
                    'message' => $this->translator->t('menus', 'menu_bar_title_to_short')
                ]
            );

        $this->validator->validate();
    }
}
