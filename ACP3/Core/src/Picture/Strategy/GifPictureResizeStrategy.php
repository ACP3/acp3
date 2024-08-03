<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Picture\Strategy;

use ACP3\Core\Picture\Input;
use ACP3\Core\Picture\Output;

class GifPictureResizeStrategy extends AbstractPictureResizeStrategy
{
    public function supportedImageType(): int
    {
        return IMAGETYPE_GIF;
    }

    public function resize(Input $input, Output $output): void
    {
        $destPicture = imagecreatetruecolor($output->getDestWidth(), $output->getDestHeight());
        if ($destPicture === false) {
            throw new \RuntimeException(\sprintf('An error occurred while creating the target picture for file "%s"!', $input->getFile()));
        }

        $srcPicture = imagecreatefromgif($input->getFile());
        if ($srcPicture === false) {
            throw new \RuntimeException(\sprintf('An error occurred while creating the source picture for file "%s"!', $input->getFile()));
        }

        $this->doResize($output, $srcPicture, $destPicture);
        imagegif($destPicture, $input->getCacheFileName());
    }
}
