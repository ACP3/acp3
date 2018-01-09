<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Pictures;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Manage extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Helpers
     */
    protected $galleryHelpers;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Validation\PictureFormValidation
     */
    protected $pictureFormValidation;
    /**
     * @var Gallery\Model\GalleryPicturesModel
     */
    protected $pictureModel;
    /**
     * @var Core\View\Block\RepositoryAwareFormBlockInterface
     */
    private $block;
    /**
     * @var Gallery\Model\Repository\GalleryRepository
     */
    private $galleryRepository;

    /**
     * Manage constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext               $context
     * @param Core\View\Block\RepositoryAwareFormBlockInterface           $block
     * @param \ACP3\Modules\ACP3\Gallery\Helpers                          $galleryHelpers
     * @param Gallery\Model\Repository\GalleryRepository                  $galleryRepository
     * @param Gallery\Model\GalleryPicturesModel                          $pictureModel
     * @param \ACP3\Modules\ACP3\Gallery\Validation\PictureFormValidation $pictureFormValidation
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\View\Block\RepositoryAwareFormBlockInterface $block,
        Gallery\Helpers $galleryHelpers,
        Gallery\Model\Repository\GalleryRepository $galleryRepository,
        Gallery\Model\GalleryPicturesModel $pictureModel,
        Gallery\Validation\PictureFormValidation $pictureFormValidation
    ) {
        parent::__construct($context);

        $this->galleryHelpers = $galleryHelpers;
        $this->pictureFormValidation = $pictureFormValidation;
        $this->pictureModel = $pictureModel;
        $this->block = $block;
        $this->galleryRepository = $galleryRepository;
    }

    /**
     * @param int      $id
     * @param int|null $galleryId
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws Core\Controller\Exception\ResultNotExistsException
     */
    public function execute(?int $id, ?int $galleryId)
    {
        if ($galleryId !== null && $this->galleryRepository->galleryExists($galleryId) === false) {
            throw new Core\Controller\Exception\ResultNotExistsException();
        }

        return $this->block
            ->setData(['gallery_id' => $galleryId])
            ->setDataById($id)
            ->setRequestData($this->request->getPost()->all())
            ->render();
    }

    /**
     * @param int|null $id
     * @param int|null $galleryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function executePost(?int $id, ?int $galleryId)
    {
        $picture = $id !== null ? $this->pictureModel->getOneById($id) : [];

        return $this->actionHelper->handleSaveAction(
            function () use ($picture, $id, $galleryId) {
                $formData = $this->request->getPost()->all();
                /** @var UploadedFile $file */
                $file = $this->request->getFiles()->get('file');

                $this->pictureFormValidation
                    ->setFileRequired(empty($picture) && $galleryId !== null)
                    ->setFile($file)
                    ->validate([]);

                if (!empty($file)) {
                    $upload = new Core\Helpers\Upload($this->appPath, Gallery\Installer\Schema::MODULE_NAME);
                    $result = $upload->moveFile($file->getPathname(), $file->getClientOriginalName());

                    if (!empty($picture)) {
                        $this->galleryHelpers->removePicture($picture['file']);
                    }

                    $formData['file'] = $result['name'];
                }

                $formData['gallery_id'] = $picture['gallery_id'] ?? $galleryId;

                return $this->pictureModel->save($formData, $id);
            },
            'acp/gallery/pictures/index/id_' . ($picture['gallery_id'] ?? $galleryId)
        );
    }
}