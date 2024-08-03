<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles\ViewProviders;

use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Helpers\Forms;
use ACP3\Core\Helpers\FormToken;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\View\Layout;
use ACP3\Modules\ACP3\Articles\Helpers;

class AdminArticleEditViewProvider
{
    public function __construct(private readonly Forms $formsHelper, private readonly FormToken $formTokenHelper, private readonly RequestInterface $request, private readonly Title $title, private readonly Translator $translator, private readonly Layout $layout)
    {
    }

    /**
     * @param array<string, mixed> $article
     *
     * @return array<string, mixed>
     */
    public function __invoke(array $article): array
    {
        $this->title->setPageTitlePrefix($article['title']);

        return [
            'active' => $this->formsHelper->yesNoCheckboxGenerator('active', $article['active']),
            'form' => array_merge($article, $this->request->getPost()->all()),
            'layouts' => $this->formsHelper->choicesGenerator(
                'layout',
                $this->getAvailableLayouts(),
                $article['layout']
            ),
            'form_token' => $this->formTokenHelper->renderFormToken(),
            'SEO_URI_PATTERN' => Helpers::URL_KEY_PATTERN,
            'SEO_ROUTE_NAME' => !empty($article['id']) ? \sprintf(Helpers::URL_KEY_PATTERN, $article['id']) : '',
        ];
    }

    /**
     * @return string[]
     */
    private function getAvailableLayouts(): array
    {
        $layoutFiles = $this->layout->getAvailableLayoutFiles();

        return [
            ...['' => $this->translator->t('articles', 'use_default_layout')],
            ...array_combine($layoutFiles, $layoutFiles),
        ];
    }
}
