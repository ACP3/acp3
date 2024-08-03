<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets;

abstract class AbstractIncludeAsset
{
    /**
     * @var array<string, true>
     */
    private array $alreadyIncluded = [];

    public function __construct(private readonly Libraries $libraries, private readonly FileResolver $fileResolver)
    {
    }

    /**
     * @param string[] $dependencies
     */
    public function add(string $moduleName, string $filePath, array $dependencies = []): string
    {
        if (!empty($dependencies)) {
            $this->libraries->enableLibraries($dependencies);
        }

        if (!$this->hasValidParams($moduleName, $filePath)) {
            throw new \InvalidArgumentException('Not all necessary arguments for the function ' . __FUNCTION__ . ' were passed!');
        }

        $key = $moduleName . '/' . $filePath;

        // Do not include the same file multiple times
        if (isset($this->alreadyIncluded[$key])) {
            return '';
        }

        $this->alreadyIncluded[$key] = true;

        return \sprintf(
            $this->getHtmlTag(),
            $this->resolvePath($moduleName, $filePath)
        );
    }

    private function hasValidParams(string $moduleName, string $filePath): bool
    {
        return preg_match('=/=', $moduleName) === 0
            && preg_match('=\./=', $filePath) === 0;
    }

    private function resolvePath(string $moduleName, string $filePath): string
    {
        $path = $this->fileResolver->getWebStaticAssetPath(
            $moduleName,
            $this->getResourceDirectory(),
            $filePath . '.' . $this->getFileExtension()
        );

        if (!$path) {
            throw new \RuntimeException(\sprintf('Could not find the requested file %s of module %s!', $filePath, $moduleName));
        }

        return $path;
    }

    abstract protected function getResourceDirectory(): string;

    abstract protected function getFileExtension(): string;

    abstract protected function getHtmlTag(): string;
}
