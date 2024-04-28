<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Composer;

use ACP3\Core\Component\ComponentRegistry;
use Composer\Script\Event;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;

class MergePackageJson
{
    /**
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     * @throws \JsonException
     */
    public static function execute(Event $event): int
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';

        $homeDir = \dirname($vendorDir);

        $basePackageJsonFile = $homeDir . '/package-base.json';

        if (!is_file($basePackageJsonFile)) {
            $io = $event->getIO();
            $io->error("Could not find package-base.json in the root ACP3 folder.\nPlease create one at first!");

            return 1;
        }

        $basePackageJson = json_decode(
            file_get_contents($basePackageJsonFile),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $result = $basePackageJson;
        $result['workspaces'] = [];
        foreach (ComponentRegistry::allTopSorted() as $component) {
            $path = $component->getPath() . '/package.json';
            if (!is_file($path)) {
                continue;
            }

            $result['workspaces'][] = substr(\dirname($path), \strlen($homeDir) + 1);

            $json = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            if (isset($json['scripts'])) {
                $result['scripts'] = array_merge($result['scripts'] ?? [], $json['scripts']);
                ksort($result['scripts']);
            }
        }

        file_put_contents(
            $homeDir . '/package.json',
            json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES ^ JSON_PRETTY_PRINT) . "\n",
        );

        return 0;
    }
}
