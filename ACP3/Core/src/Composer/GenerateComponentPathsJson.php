<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Composer;

use ACP3\Core\Component\ComponentRegistry;
use Composer\Script\Event;

class GenerateComponentPathsJson
{
    /**
     * @throws \JsonException
     */
    public static function execute(Event $event): int
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        require $vendorDir . '/autoload.php';

        $homeDir = \dirname($vendorDir);

        $paths = [];
        foreach (ComponentRegistry::all() as $component) {
            if (\array_key_exists($component->getComponentType()->value, $paths) === false) {
                $paths[$component->getComponentType()->value] = [];
            }

            $path = $component->getPath() . '/composer.json';
            $content = file_get_contents($path);

            if ($content === false) {
                throw new \RuntimeException(\sprintf('Could not read file "%s"!', $path));
            }

            $composer = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $paths[$component->getComponentType()->value][$composer['name']] = str_replace([$homeDir, '\\'], ['.', '/'], $component->getPath());
        }

        $result = file_put_contents(
            $homeDir . '/.component-paths.json',
            json_encode($paths, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES ^ JSON_PRETTY_PRINT)
        );

        return $result !== false ? 0 : 1;
    }
}
