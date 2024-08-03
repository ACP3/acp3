<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Settings;

use ACP3\Core\Repository\ModuleAwareRepositoryInterface;
use ACP3\Core\Settings\Event\SettingsSaveEvent;
use ACP3\Core\Settings\Repository\SettingsAwareRepositoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages the various module settings.
 */
class Settings implements SettingsInterface
{
    private const CACHE_ID = 'settings';
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $settings = [];

    public function __construct(private readonly EventDispatcherInterface $eventDispatcher, private readonly CacheItemPoolInterface $coreCachePool, private readonly ModuleAwareRepositoryInterface $systemModuleRepository, private readonly SettingsAwareRepositoryInterface $systemSettingsRepository)
    {
    }

    public function saveSettings(array $data, string $moduleName): bool
    {
        $moduleId = $this->systemModuleRepository->getModuleId($moduleName);

        if (empty($moduleId)) {
            throw new \InvalidArgumentException(\sprintf('The module "%s" is not installed.', $moduleName));
        }

        $settingsSaveEvent = new SettingsSaveEvent($moduleName, $data);

        $this->eventDispatcher->dispatch(
            $settingsSaveEvent,
            'core.settings.save_before'
        );
        $this->eventDispatcher->dispatch(
            $settingsSaveEvent,
            $moduleName . '.settings.save_before'
        );

        $result = false;

        foreach ($settingsSaveEvent->getData() as $key => $value) {
            $updateValues = [
                'value' => $value,
            ];
            $where = [
                'module_id' => $moduleId,
                'name' => $key,
            ];
            $result = $this->systemSettingsRepository->update($updateValues, $where);
        }

        $result2 = $this->coreCachePool->deleteItem(self::CACHE_ID);

        return $result !== false && $result2 !== false;
    }

    public function getSettings(string $module): array
    {
        if ($this->settings === []) {
            $cacheItem = $this->coreCachePool->getItem(self::CACHE_ID);

            if (!$cacheItem->isHit()) {
                $settings = $this->systemSettingsRepository->getAllSettings();

                $data = [];
                foreach ($settings as $setting) {
                    $data[$setting['module_name']][$setting['name']] = $setting['value'];
                }

                $cacheItem->set($data);
                $this->coreCachePool->saveDeferred($cacheItem);
            }

            $this->settings = $cacheItem->get();
        }

        return $this->settings[$module] ?? [];
    }
}
