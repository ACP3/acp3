<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Core\Environment;

use ACP3\Core\Component\ComponentRegistry;
use ACP3\Core\Component\ComponentTypeEnum;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Environment\ThemePathInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\System\Installer\Schema;
use Composer\InstalledVersions;

class Theme implements ThemePathInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $availableThemes = [];
    /**
     * @var array<string, string[]>
     */
    private array $sortedThemeDependencies = [];

    public function __construct(private readonly ApplicationPath $appPath, private readonly SettingsInterface $settings)
    {
    }

    /**
     * @throws \JsonException
     */
    public function getAvailableThemes(): array
    {
        if (empty($this->availableThemes)) {
            $this->setAvailableThemes();
        }

        return $this->availableThemes;
    }

    /**
     * @throws \JsonException
     */
    private function setAvailableThemes(): void
    {
        $registeredThemes = ComponentRegistry::filterByType(ComponentRegistry::all(), [ComponentTypeEnum::THEME]);

        foreach ($registeredThemes as $registeredTheme) {
            $this->addTheme($registeredTheme->getPath(), $registeredTheme->getName());
        }
    }

    /**
     * @throws \JsonException
     */
    private function addTheme(string $themePath, string $themeName): void
    {
        if (\array_key_exists($themeName, $this->availableThemes)) {
            return;
        }

        $path = $themePath . DIRECTORY_SEPARATOR . 'composer.json';
        $content = file_get_contents($path);

        if (!$content) {
            throw new \RuntimeException(\sprintf('Could not load file "%s"!', $path));
        }

        $composerData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        $this->availableThemes[$themeName] = [
            'author' => $this->getAuthors($composerData),
            'css' => $composerData['extra']['css'] ?? [],
            'description' => $composerData['description'],
            'internal_name' => $themeName,
            'js' => $composerData['extra']['js'] ?? [],
            'name' => $composerData['name'],
            'libraries' => $composerData['extra']['libraries'] ?? [],
            'parent' => $composerData['extra']['parent'] ?? null,
            'path' => $themePath,
            'version' => InstalledVersions::getPrettyVersion($composerData['name']) ?: InstalledVersions::getRootPackage()['pretty_version'],
            'web_path' => $this->appPath->getWebRoot() . str_replace([ACP3_ROOT_DIR, '\\'], ['', '/'], $themeName),
        ];
    }

    /**
     * @param array<string, mixed> $composerData
     */
    private function getAuthors(array $composerData): string
    {
        $authors = [];

        foreach ($composerData['authors'] ?? [] as $author) {
            $authors[] = $author['name'];
        }

        return implode(', ', $authors);
    }

    /**
     * @throws \JsonException
     */
    public function has(string $themeName): bool
    {
        return \array_key_exists($themeName, $this->getAvailableThemes());
    }

    public function getCurrentTheme(): string
    {
        return $this->settings->getSettings(Schema::MODULE_NAME)['design'];
    }

    /**
     * @throws \JsonException
     */
    public function getCurrentThemeDependencies(): array
    {
        return $this->getThemeDependencies($this->getCurrentTheme());
    }

    /**
     * @throws \JsonException
     */
    public function getThemeDependencies(string $themeName): array
    {
        if (!isset($this->sortedThemeDependencies[$themeName])) {
            $this->setThemeDependencies($themeName);
        }

        return $this->sortedThemeDependencies[$themeName];
    }

    /**
     * @throws \JsonException
     */
    private function setThemeDependencies(string $themeName): void
    {
        $availableThemes = $this->getAvailableThemes();

        if (!isset($availableThemes[$themeName]['parent'])) {
            $this->sortedThemeDependencies[$themeName] = [$themeName];

            return;
        }

        $currentTheme = $themeName;

        $parents = [$themeName];
        do {
            $currentTheme = $availableThemes[$currentTheme]['parent'];
            $parents[] = $currentTheme;
        } while (isset($availableThemes[$currentTheme]['parent']));

        $this->sortedThemeDependencies[$themeName] = $parents;
    }

    /**
     * @throws \JsonException
     */
    public function getDesignPathInternal(?string $themeName = null): string
    {
        return $this->getAvailableThemes()[$themeName ?? $this->getCurrentTheme()]['path'];
    }

    /**
     * @throws \JsonException
     */
    public function getDesignPathWeb(?string $themeName = null): string
    {
        return $this->getAvailableThemes()[$themeName ?? $this->getCurrentTheme()]['web_path'];
    }

    public function getCurrentThemeInfo(): array
    {
        return $this->availableThemes[$this->getCurrentTheme()];
    }
}
