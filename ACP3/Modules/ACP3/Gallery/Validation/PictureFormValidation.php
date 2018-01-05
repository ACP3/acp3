<?php
namespace ACP3\Modules\ACP3\Gallery\Validation;

use ACP3\Core;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PictureFormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @var bool
     */
    protected $fileRequired = false;
    /**
     * @var UploadedFile|null
     */
    protected $file;

    /**
     * @param boolean $fileRequired
     *
     * @return $this
     */
    public function setFileRequired($fileRequired)
    {
        $this->fileRequired = (bool)$fileRequired;

        return $this;
    }

    /**
     * @param UploadedFile|null $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                Core\Validation\ValidationRules\PictureValidationRule::class,
                [
                    'data' => $this->file,
                    'field' => 'file',
                    'message' => $this->translator->t('gallery', 'invalid_image_selected'),
                    'extra' => [
                        'required' => $this->fileRequired,
                    ],
                ]
            );

        $this->validator->validate();
    }
}
