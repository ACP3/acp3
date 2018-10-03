<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Validation\ValidationRules;

use ACP3\Core\Modules;
use ACP3\Core\Validation\ValidationRules\AbstractValidationRule;
use ACP3\Core\Validation\ValidationRules\InternalUriValidationRule;
use ACP3\Modules\ACP3\Articles\Validation\ValidationRules\ArticleExistsValidationRule;

class LinkModeValidationRule extends AbstractValidationRule
{
    /**
     * @var \ACP3\Core\Modules
     */
    protected $modules;
    /**
     * @var \ACP3\Core\Validation\ValidationRules\InternalUriValidationRule
     */
    protected $internalUriValidationRule;
    /**
     * @var \ACP3\Modules\ACP3\Articles\Validation\ValidationRules\ArticleExistsValidationRule
     */
    protected $articleExistsValidationRule;

    /**
     * LinkModeValidationRule constructor.
     *
     * @param \ACP3\Core\Modules                                                                      $modules
     * @param \ACP3\Core\Validation\ValidationRules\InternalUriValidationRule                         $internalUriValidationRule
     * @param \ACP3\Modules\ACP3\Articles\Validation\ValidationRules\ArticleExistsValidationRule|null $articleExistsValidationRule
     */
    public function __construct(
        Modules $modules,
        InternalUriValidationRule $internalUriValidationRule,
        ?ArticleExistsValidationRule $articleExistsValidationRule = null
    ) {
        $this->modules = $modules;
        $this->internalUriValidationRule = $internalUriValidationRule;
        $this->articleExistsValidationRule = $articleExistsValidationRule;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \is_array($field)) {
            $mode = \reset($field);
            $moduleName = \next($field);
            $uri = \next($field);
            $articleId = \next($field);

            return $this->isValidLink($data[$mode], $data[$moduleName], $data[$uri], $data[$articleId]);
        }

        return false;
    }

    /**
     * @param int    $mode
     * @param string $moduleName
     * @param string $uri
     * @param int    $articleId
     *
     * @return bool
     */
    protected function isValidLink(int $mode, string $moduleName, string $uri, int $articleId)
    {
        switch ($mode) {
            case 1:
                return $this->modules->isActive($moduleName);
            case 2:
                return $this->internalUriValidationRule->isValid($uri);
            case 3:
                return !empty($uri);
            case 4:
                if ($this->modules->isActive('articles')) {
                    return $this->articleExistsValidationRule->isValid($articleId);
                }
        }

        return false;
    }
}
