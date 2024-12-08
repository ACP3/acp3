<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Core\Breadcrumb;

use ACP3\Core\Breadcrumb\Event\GetSiteAndPageTitleBeforeEvent;
use ACP3\Core\Breadcrumb\Steps;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Title extends \ACP3\Core\Breadcrumb\Title
{
    /**
     * @var array<string, mixed>
     */
    private array $systemSettings = [];

    public function __construct(
        private readonly RequestInterface $request,
        Steps $steps,
        EventDispatcherInterface $eventDispatcher,
        private readonly SettingsInterface $settings,
    ) {
        parent::__construct($steps, $eventDispatcher);
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettings(): array
    {
        if (empty($this->systemSettings)) {
            $this->systemSettings = $this->settings->getSettings(Schema::MODULE_NAME);
        }

        return $this->systemSettings;
    }

    public function getSiteAndPageTitle(): string
    {
        if ($this->request->isHomepage()) {
            return $this->renderHomepageTitle();
        }

        $settings = $this->getSettings();
        if ($this->allowSystemSubtitle() && $settings['site_subtitle_mode'] == 2) {
            $this->setSiteSubtitle('');
        }

        return parent::getSiteAndPageTitle();
    }

    private function renderHomepageTitle(): string
    {
        $this->eventDispatcher->dispatch(
            new GetSiteAndPageTitleBeforeEvent($this),
            GetSiteAndPageTitleBeforeEvent::NAME
        );

        if ($this->allowSystemSubtitle()) {
            $settings = $this->getSettings();

            if ($settings['site_subtitle_homepage_mode'] == 1) {
                $this->setMetaTitle($this->getSiteSubtitle());
                $this->setSiteSubtitle('');
            }
        }

        return $this->renderSiteTitle()
            . $this->renderSiteSubTitle()
            . $this->renderPageTitlePrefix()
            . $this->renderPageTitle()
            . $this->renderPageTitleSuffix();
    }

    private function allowSystemSubtitle(): bool
    {
        return $this->getSettings()['site_subtitle_mode'] != 3;
    }

    protected function renderSiteSubTitle(): string
    {
        if ($this->allowSystemSubtitle()) {
            if ($this->request->isHomepage() && !empty($this->getSiteSubtitle())) {
                return $this->getSiteSubtitle() . $this->getPageTitleSeparator();
            }

            return parent::renderSiteSubTitle();
        }

        return '';
    }

    protected function renderSiteTitle(): string
    {
        if ($this->request->isHomepage() && !empty($this->getSiteTitle())) {
            return $this->getSiteTitle() . $this->getPageTitleSeparator();
        }

        return parent::renderSiteTitle();
    }
}
