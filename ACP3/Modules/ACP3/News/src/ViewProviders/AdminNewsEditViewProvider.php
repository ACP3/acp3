<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\ViewProviders;

use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Helpers\Forms;
use ACP3\Core\Helpers\FormToken;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Categories\Helpers as CategoriesHelpers;
use ACP3\Modules\ACP3\News\Helpers as NewsHelpers;
use ACP3\Modules\ACP3\News\Installer\Schema as NewsSchema;

class AdminNewsEditViewProvider
{
    public function __construct(private readonly CategoriesHelpers $categoriesHelpers, private readonly Forms $formsHelper, private readonly FormToken $formTokenHelper, private readonly RequestInterface $request, private readonly SettingsInterface $settings, private readonly Title $title, private readonly Translator $translator)
    {
    }

    /**
     * @param array<string, mixed> $news
     *
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(array $news): array
    {
        $this->title->setPageTitlePrefix($news['title']);

        return [
            'active' => $this->formsHelper->yesNoCheckboxGenerator('active', $news['active']),
            'categories' => $this->categoriesHelpers->categoriesList(
                NewsSchema::MODULE_NAME,
                $news['category_id'],
                true
            ),
            'options' => $this->fetchOptions($news['readmore']),
            'target' => $this->formsHelper->linkTargetChoicesGenerator('target', $news['target']),
            'form' => array_merge($news, $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken(),
            'SEO_URI_PATTERN' => NewsHelpers::URL_KEY_PATTERN,
            'SEO_ROUTE_NAME' => !empty($news['id']) ? \sprintf(NewsHelpers::URL_KEY_PATTERN, $news['id']) : '',
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    private function fetchOptions(int $readMoreValue): array
    {
        $settings = $this->settings->getSettings(NewsSchema::MODULE_NAME);
        $options = [];
        if ($settings['readmore'] == 1) {
            $readMore = [
                '1' => $this->translator->t('news', 'activate_readmore'),
            ];

            $options = $this->formsHelper->checkboxGenerator('readmore', $readMore, $readMoreValue);
        }

        return $options;
    }
}
