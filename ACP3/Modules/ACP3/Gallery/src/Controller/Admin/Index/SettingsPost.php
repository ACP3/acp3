<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Gallery;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Response;

class SettingsPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly FormAction $actionHelper,
        private readonly CacheItemPoolInterface $galleryCachePool,
        private readonly Core\Helpers\Secure $secureHelper,
        private readonly Gallery\Validation\AdminSettingsFormValidation $adminSettingsFormValidation,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(): array|string|Response
    {
        return $this->actionHelper->handleSettingsPostAction(function () {
            $formData = $this->request->getPost()->all();

            $this->adminSettingsFormValidation->validate($formData);

            $data = [
                'width' => (int) $formData['width'],
                'height' => (int) $formData['height'],
                'thumbwidth' => (int) $formData['thumbwidth'],
                'thumbheight' => (int) $formData['thumbheight'],
                'dateformat' => $this->secureHelper->strEncode($formData['dateformat']),
                'sidebar' => (int) $formData['sidebar'],
            ];

            $bool = $this->config->saveSettings($data, Gallery\Installer\Schema::MODULE_NAME);

            if ($this->hasImageDimensionChanges($formData)) {
                Core\Cache\Purge::doPurge($this->applicationPath->getUploadsDir() . 'gallery/cache', 'gallery');

                $this->galleryCachePool->clear();
            }

            return $bool;
        });
    }

    /**
     * @param array<string, mixed> $formData
     */
    private function hasImageDimensionChanges(array $formData): bool
    {
        $settings = $this->config->getSettings(Gallery\Installer\Schema::MODULE_NAME);

        return $formData['thumbwidth'] !== $settings['thumbwidth']
            || $formData['thumbheight'] !== $settings['thumbheight']
            || $formData['width'] !== $settings['width']
            || $formData['height'] !== $settings['height'];
    }
}
