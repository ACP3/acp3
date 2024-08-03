<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Picture\Strategy;

use ACP3\Core\Picture\Output;

abstract class AbstractPictureResizeStrategy implements PictureResizeStrategyInterface
{
    protected function doResize(Output $output, \GdImage $srcImage, \GdImage $destImage): void
    {
        $result = imagecopyresampled(
            $destImage,
            $srcImage,
            0,
            0,
            0,
            0,
            $output->getDestWidth(),
            $output->getDestHeight(),
            $output->getSrcWidth(),
            $output->getSrcHeight()
        );

        if (!$result) {
            throw new \RuntimeException(\sprintf('Could not resize image %s', $output->getSrcFile()));
        }
    }
}
