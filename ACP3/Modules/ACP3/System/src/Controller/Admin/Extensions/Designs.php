<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Controller\Admin\Extensions;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Environment\ThemePathInterface;
use ACP3\Core\Helpers\RedirectMessages;
use ACP3\Modules\ACP3\System\Installer\Schema;
use ACP3\Modules\ACP3\System\Services\CacheClearService;
use ACP3\Modules\ACP3\System\ViewProviders\AdminThemesViewProvider;
use Symfony\Component\HttpFoundation\Response;

class Designs extends AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly AdminThemesViewProvider $adminThemesViewProvider,
        private readonly ThemePathInterface $theme,
        private readonly RedirectMessages $redirectMessages,
        private readonly CacheClearService $cacheClearService,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|Response
     */
    public function __invoke(?string $dir = null): array|Response
    {
        if (!empty($dir)) {
            return $this->doUpdateTheme($dir);
        }

        return ($this->adminThemesViewProvider)();
    }

    private function doUpdateTheme(string $design): Response
    {
        $result = false;

        if ($this->theme->has($design)) {
            $result = $this->config->saveSettings(['design' => $design], Schema::MODULE_NAME);

            $this->cacheClearService->clearCacheByType('templates');
            $this->cacheClearService->clearCacheByType('general');
        }

        $text = $this->translator->t('system', $result === true ? 'designs_edit_success' : 'designs_edit_error');

        return $this->redirectMessages->setMessage($result, $text, $this->request->getFullPath());
    }
}
