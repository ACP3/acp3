<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Articles\View\Block\Admin;

use ACP3\Core\View\Block\AbstractRepositoryAwareFormBlock;
use ACP3\Modules\ACP3\Articles\Helpers;

class ArticleAdminFormBlock extends AbstractRepositoryAwareFormBlock
{
    /**
     * @inheritdoc
     */
    public function getDefaultData(): array
    {
        return [
            'active' => 1,
            'end' => '',
            'id' => '',
            'start' => '',
            'text' => '',
            'title' => '',
        ];
    }

    /**
     * @inheritdoc
     */
    public function render()
    {
        $data = $this->getData();

        $this->breadcrumb->setLastStepReplacement(
            $this->translator->t('articles', !$this->getId() ? 'admin_index_create' : 'admin_index_edit')
        );
        $this->title->setPageTitlePrefix($data['title']);

        return [
            'active' => $this->forms->yesNoCheckboxGenerator('active', $data['active']),
            'form' => \array_merge($data, $this->getRequestData()),
            'form_token' => $this->formToken->renderFormToken(),
            'SEO_URI_PATTERN' => Helpers::URL_KEY_PATTERN,
            'SEO_ROUTE_NAME' => $this->getSeoRouteName($this->getId()),
        ];
    }

    /**
     * @param int|null $id
     * @return string
     */
    private function getSeoRouteName(?int $id): string
    {
        return !empty($id) ? \sprintf(Helpers::URL_KEY_PATTERN, $id) : '';
    }
}