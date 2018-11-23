<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation\ValidationRules;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureValidationRule extends AbstractValidationRule
{
    /**
     * @var \ACP3\Core\Validation\ValidationRules\FileUploadValidationRule
     */
    protected $fileUploadValidationRule;

    /**
     * PictureValidationRule constructor.
     *
     * @param \ACP3\Core\Validation\ValidationRules\FileUploadValidationRule $fileUploadValidationRule
     */
    public function __construct(FileUploadValidationRule $fileUploadValidationRule)
    {
        $this->fileUploadValidationRule = $fileUploadValidationRule;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($data, $field = '', array $extra = []): bool
    {
        $params = \array_merge([
            'width' => 0,
            'height' => 0,
            'filesize' => 0,
            'required' => true,
        ], $extra);

        if ($this->fileUploadValidationRule->isValid($data)) {
            return $this->isPicture(
                $data instanceof UploadedFile ? $data->getPathname() : $data['tmp_name'],
                $params['width'],
                $params['height'],
                $params['filesize']
            );
        } elseif ($params['required'] === false && empty($data)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $file
     * @param int    $width
     * @param int    $height
     * @param int    $filesize
     *
     * @return bool
     */
    protected function isPicture($file, $width = 0, $height = 0, $filesize = 0)
    {
        $info = \getimagesize($file);
        $isPicture = ($info[2] >= 1 && $info[2] <= 3);

        if ($isPicture === true) {
            $bool = true;
            // Optional parameters
            if ($this->validateOptionalParameters($file, $info, $width, $height, $filesize)) {
                $bool = false;
            }

            return $bool;
        }

        return false;
    }

    /**
     * @param string $file
     * @param array  $info
     * @param int    $width
     * @param int    $height
     * @param int    $filesize
     *
     * @return bool
     */
    protected function validateOptionalParameters($file, array $info, $width, $height, $filesize)
    {
        return $width > 0 && $info[0] > $width ||
        $height > 0 && $info[1] > $height ||
        $filesize > 0 && \filesize($file) > $filesize;
    }
}
