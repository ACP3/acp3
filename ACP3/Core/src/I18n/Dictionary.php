<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\I18n;

use ACP3\Core\Component\ComponentRegistry;
use ACP3\Core\Component\ComponentTypeEnum;
use ACP3\Core\Environment\ThemePathInterface;
use Fisharebest\Localization\Locale;

class Dictionary implements DictionaryInterface
{
    public function __construct(private readonly ThemePathInterface $theme)
    {
    }

    /**
     * @throws \MJS\TopSort\CircularDependencyException
     * @throws \MJS\TopSort\ElementNotFoundException
     */
    public function getDictionary(string $language): array
    {
        $locale = Locale::create($language);
        $data = [
            'info' => [
                'direction' => $locale->script()->direction(),
            ],
            'keys' => [],
        ];

        $components = ComponentRegistry::filterByType(
            ComponentRegistry::allTopSorted(),
            [
                ComponentTypeEnum::CORE,
                ComponentTypeEnum::MODULE,
                ComponentTypeEnum::INSTALLER,
            ]
        );

        foreach ($components as $component) {
            $i18nFile = "{$component->getPath()}/Resources/i18n/{$language}.xml";

            if (is_file($i18nFile) === false) {
                continue;
            }

            $data['keys'] += $this->parseI18nFile($i18nFile, $component->getName());
        }

        $themeDependenciesReversed = array_reverse($this->theme->getCurrentThemeDependencies());
        foreach ($themeDependenciesReversed as $theme) {
            $i18nFiles = glob($this->theme->getDesignPathInternal($theme) . "/*/Resources/i18n/{$language}.xml");

            if ($i18nFiles === false) {
                continue;
            }

            foreach ($i18nFiles as $i18nFile) {
                $data['keys'] = [...$data['keys'], ...$this->parseI18nFile($i18nFile, $this->getModuleNameFromThemePath($i18nFile))];
            }
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    private function parseI18nFile(string $i18nFile, string $moduleName): array
    {
        $data = [];
        $fileContent = file_get_contents($i18nFile);

        if ($fileContent === false) {
            throw new \RuntimeException(\sprintf('An error occurred while loading file "%s"!', $i18nFile));
        }

        $xml = simplexml_load_string($fileContent);

        if ($xml) {
            foreach ($xml->keys->item as $item) {
                $data[strtolower($moduleName . $item['key'])] = trim((string) $item);
            }
        }

        return $data;
    }

    private function getModuleNameFromThemePath(string $filePath): string
    {
        $pathArray = explode('/', $filePath);

        return $pathArray[\count($pathArray) - 4];
    }

    public function getLanguagePacks(): array
    {
        $languagePacks = [];

        foreach (ComponentRegistry::all() as $component) {
            $languageFiles = glob($component->getPath() . '/Resources/i18n/*.xml');

            if ($languageFiles === false) {
                continue;
            }

            foreach ($languageFiles as $file) {
                $isoCode = $this->getLanguagePackIsoCode($file);

                if (isset($languagePacks[$isoCode])) {
                    continue;
                }

                try {
                    $languagePacks[$isoCode] = $this->getLanguagePack($isoCode);
                } catch (\DomainException) {
                    // Intentionally omitted
                }
            }
        }

        return $languagePacks;
    }

    /**
     * @return array{iso: string, name: string}
     *
     * @throws \DomainException
     */
    private function getLanguagePack(string $languageIsoCode): array
    {
        return [
            'iso' => $languageIsoCode,
            'name' => Locale::create($languageIsoCode)->endonym(),
        ];
    }

    private function getLanguagePackIsoCode(string $filePath): string
    {
        return pathinfo($filePath)['filename'];
    }
}
