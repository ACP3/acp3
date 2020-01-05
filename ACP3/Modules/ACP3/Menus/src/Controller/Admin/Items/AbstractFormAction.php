<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Controller\Admin\Items;

use ACP3\Core\Controller\AbstractFrontendAction;
use ACP3\Core\Controller\Context\FrontendContext;
use ACP3\Core\Helpers\Forms;
use ACP3\Modules\ACP3\Articles;
use ACP3\Modules\ACP3\Menus;

abstract class AbstractFormAction extends AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Articles\Helpers
     */
    protected $articlesHelpers;
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    protected $formsHelper;

    /**
     * AbstractFormAction constructor.
     *
     * @param \ACP3\Modules\ACP3\Articles\Helpers|null $articlesHelpers
     */
    public function __construct(
        FrontendContext $context,
        Forms $formsHelper,
        ?Articles\Helpers $articlesHelpers = null)
    {
        parent::__construct($context);

        $this->formsHelper = $formsHelper;
        $this->articlesHelpers = $articlesHelpers;
    }

    /**
     * @return string
     */
    protected function fetchMenuItemModeForSave(array $formData)
    {
        return ($formData['mode'] == 2 || $formData['mode'] == 3) && \preg_match(
            Menus\Helpers\MenuItemsList::ARTICLES_URL_KEY_REGEX,
            $formData['uri']
        ) ? '4' : $formData['mode'];
    }

    /**
     * @return string
     */
    protected function fetchMenuItemUriForSave(array $formData)
    {
        return $formData['mode'] == 1 ? $formData['module'] : ($formData['mode'] == 4 ? \sprintf(
            Articles\Helpers::URL_KEY_PATTERN,
            $formData['articles']
        ) : $formData['uri']);
    }

    /**
     * @param string $value
     *
     * @return array
     */
    protected function fetchMenuItemTypes($value = '')
    {
        $menuItemTypes = [
            1 => $this->translator->t('menus', 'module'),
            2 => $this->translator->t('menus', 'dynamic_page'),
            3 => $this->translator->t('menus', 'hyperlink'),
        ];
        if ($this->articlesHelpers) {
            $menuItemTypes[4] = $this->translator->t('menus', 'article');
        }

        return $this->formsHelper->choicesGenerator('mode', $menuItemTypes, $value);
    }

    protected function fetchModules(array $menuItem = []): array
    {
        $modules = $this->modules->getAllModulesAlphabeticallySorted();
        foreach ($modules as $row) {
            $modules[$row['name']]['selected'] = $this->formsHelper->selectEntry(
                'module',
                $row['name'],
                !empty($menuItem) && $menuItem['mode'] == 1 ? $menuItem['uri'] : ''
            );
        }

        return $modules;
    }
}