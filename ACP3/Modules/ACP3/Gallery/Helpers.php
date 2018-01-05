<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery\Installer\Schema;

class Helpers
{
    const URL_KEY_PATTERN_GALLERY = 'gallery/index/pics/id_%s/';
    const URL_KEY_PATTERN_PICTURE = 'gallery/index/details/id_%s/';

    /**
     * @var \ACP3\Core\Environment\ApplicationPath
     */
    protected $appPath;

    /**
     * Helpers constructor.
     *
     * @param \ACP3\Core\Environment\ApplicationPath $appPath
     */
    public function __construct(Core\Environment\ApplicationPath $appPath)
    {
        $this->appPath = $appPath;
    }

    /**
     * Löscht ein Bild aus dem Dateisystem
     *
     * @param string $file
     */
    public function removePicture($file)
    {
        $upload = new Core\Helpers\Upload($this->appPath, 'cache/images');

        $upload->removeUploadedFile('gallery_thumb_' . $file);
        $upload->removeUploadedFile('gallery_' . $file);

        $upload = new Core\Helpers\Upload($this->appPath, Schema::MODULE_NAME);
        $upload->removeUploadedFile($file);
    }
}
