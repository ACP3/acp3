<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Picture;

use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Picture\Exception\CacheDirectoryCreationException;
use ACP3\Core\Picture\Exception\PictureGenerateException;
use ACP3\Core\Picture\Exception\UnsupportedPictureTypeException;
use ACP3\Core\Picture\Strategy\GifPictureResizeStrategy;
use ACP3\Core\Picture\Strategy\JpegPictureResizeStrategy;
use ACP3\Core\Picture\Strategy\PictureResizeStrategyInterface;
use ACP3\Core\Picture\Strategy\PngPictureResizeStrategy;
use ACP3\Core\Picture\Strategy\WebpPictureResizeStrategy;
use FastImageSize\FastImageSize;

class Picture
{
    /**
     * @var PictureResizeStrategyInterface[]
     */
    private array $strategies = [];

    public function __construct(private readonly FastImageSize $fastImageSize, private readonly ApplicationPath $appPath)
    {
        $this->addStrategy(new GifPictureResizeStrategy());
        $this->addStrategy(new JpegPictureResizeStrategy());
        $this->addStrategy(new PngPictureResizeStrategy());
        $this->addStrategy(new WebpPictureResizeStrategy());
    }

    private function addStrategy(PictureResizeStrategyInterface $strategy): void
    {
        $this->strategies[$strategy->supportedImageType()] = $strategy;
    }

    /**
     * @throws PictureGenerateException
     */
    public function process(Input $input): Output
    {
        if (is_file($input->getFile()) === false) {
            throw new PictureGenerateException(\sprintf('Could not find picture: %s', $input->getFile()));
        }

        $cacheFile = $input->getCacheFileName();

        $picInfo = $this->getPictureInfo($input->getFile());

        $output = new Output($this->appPath, $input->getFile(), $picInfo['type']);
        $output->setSrcWidth($picInfo['width']);
        $output->setSrcHeight($picInfo['height']);

        $this->calcDestDimensions($input, $output);

        // Direct output of the picture, if it is already cached
        if ($input->isEnableCache() === true && is_file($cacheFile) === true) {
            $output->setDestFile($cacheFile);
        } elseif ($this->resamplingIsNecessary($input, $output)) {
            $this->createCacheDir($input);
            $this->resample($input, $output);

            $output->setDestFile($cacheFile);
        }

        return $output;
    }

    /**
     * @return array{width: int, height: int, type: int}
     *
     * @throws PictureGenerateException
     */
    private function getPictureInfo(string $fileName): array
    {
        /** @var array{width: int, height: int, type: int}|false $picInfo */
        $picInfo = $this->fastImageSize->getImageSize($fileName);

        // If fastImageSize fails, try it with PHP's standard getimagesize() function
        if ($picInfo === false) {
            $info = getimagesize($fileName);

            if ($info === false) {
                throw new PictureGenerateException(\sprintf('Could not get image size information for picture <%s>!', $fileName));
            }

            $picInfo = [
                'width' => $info[0],
                'height' => $info[1],
                'type' => $info[2],
            ];
        }

        return $picInfo;
    }

    private function resamplingIsNecessary(Input $input, Output $output): bool
    {
        return ($input->isForceResample() || $this->hasNecessaryResamplingDimensions($input, $output))
            && \array_key_exists($output->getType(), $this->strategies);
    }

    private function hasNecessaryResamplingDimensions(Input $input, Output $output): bool
    {
        return ($input->getMaxWidth() > 0 || $input->getMaxHeight() > 0) && ($output->getSrcWidth() > $input->getMaxWidth() || $output->getSrcHeight() > $input->getMaxHeight());
    }

    /**
     * Calculates the targeted picture dimensions.
     */
    private function calcDestDimensions(Input $input, Output $output): void
    {
        if ($input->getMaxWidth() === 0 && $input->getMaxHeight() === 0) {
            return;
        }

        if ($input->isPreferHeight() === false && ($output->getSrcWidth() >= $output->getSrcHeight() || $input->isPreferWidth() === true)) {
            $destWidth = $input->getMaxWidth();
            $destHeight = (int) ($output->getSrcHeight() * $destWidth / $output->getSrcWidth());
        } else {
            $destHeight = $input->getMaxHeight();
            $destWidth = (int) ($output->getSrcWidth() * $destHeight / $output->getSrcHeight());
        }

        $output->setDestWidth($destWidth);
        $output->setDestHeight($destHeight);
    }

    /**
     * Creates the cache directory if it's not already present.
     *
     * @throws PictureGenerateException
     */
    private function createCacheDir(Input $input): void
    {
        if (is_dir($input->getCacheDir())) {
            return;
        }

        if (!@mkdir($concurrentDirectory = $input->getCacheDir()) && !is_dir($concurrentDirectory)) {
            throw new CacheDirectoryCreationException(\sprintf('Could not create cache dir: %s', $input->getCacheDir()));
        }
    }

    /**
     * Resamples the picture to the given values.
     *
     * @throws UnsupportedPictureTypeException
     */
    private function resample(Input $input, Output $output): void
    {
        if (!\array_key_exists($output->getType(), $this->strategies)) {
            throw new UnsupportedPictureTypeException(\sprintf('Could not resize the picture %s, as it is using an unsupported file type!', $input->getFile()));
        }

        $this->strategies[$output->getType()]->resize($input, $output);
    }
}
