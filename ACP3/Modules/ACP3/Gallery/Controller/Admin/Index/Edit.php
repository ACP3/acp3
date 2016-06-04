<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;
use ACP3\Modules\ACP3\Seo\Core\Router\Aliases;
use ACP3\Modules\ACP3\Seo\Helper\MetaStatements;

/**
 * Class Edit
 * @package ACP3\Modules\ACP3\Gallery\Controller\Admin\Index
 */
class Edit extends AbstractFormAction
{
    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Model\GalleryRepository
     */
    protected $galleryRepository;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Validation\GalleryFormValidation
     */
    protected $galleryFormValidation;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Model\PictureRepository
     */
    protected $pictureRepository;
    /**
     * @var \ACP3\Modules\ACP3\Seo\Core\Router\Aliases
     */
    protected $aliases;
    /**
     * @var \ACP3\Modules\ACP3\Seo\Helper\MetaStatements
     */
    protected $metaStatements;

    /**
     * Edit constructor.
     *
     * @param \ACP3\Core\Controller\Context\AdminContext                  $context
     * @param \ACP3\Core\Date                                             $date
     * @param \ACP3\Core\Helpers\FormToken                                $formTokenHelper
     * @param \ACP3\Modules\ACP3\Gallery\Model\GalleryRepository          $galleryRepository
     * @param \ACP3\Modules\ACP3\Gallery\Model\PictureRepository          $pictureRepository
     * @param \ACP3\Modules\ACP3\Gallery\Validation\GalleryFormValidation $galleryFormValidation
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Core\Date $date,
        Core\Helpers\FormToken $formTokenHelper,
        Gallery\Model\GalleryRepository $galleryRepository,
        Gallery\Model\PictureRepository $pictureRepository,
        Gallery\Validation\GalleryFormValidation $galleryFormValidation
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->formTokenHelper = $formTokenHelper;
        $this->galleryRepository = $galleryRepository;
        $this->pictureRepository = $pictureRepository;
        $this->galleryFormValidation = $galleryFormValidation;
    }

    /**
     * @param \ACP3\Modules\ACP3\Seo\Core\Router\Aliases $aliases
     */
    public function setAliases(Aliases $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * @param \ACP3\Modules\ACP3\Seo\Helper\MetaStatements $metaStatements
     */
    public function setMetaStatements(MetaStatements $metaStatements)
    {
        $this->metaStatements = $metaStatements;
    }

    /**
     * @param int $id
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($id)
    {
        if ($this->galleryRepository->galleryExists($id) === true) {
            $gallery = $this->galleryRepository->getGalleryById($id);

            $this->title->setPageTitlePostfix($gallery['title']);

            if ($this->request->getPost()->count() !== 0) {
                return $this->executePost($this->request->getPost()->all(), $id);
            }

            return array_merge(
                [
                    'SEO_FORM_FIELDS' => $this->metaFormFieldsHelper
                        ? $this->metaFormFieldsHelper->formFields(sprintf(Gallery\Helpers::URL_KEY_PATTERN_GALLERY, $id))
                        : [],
                    'gallery_id' => $id,
                    'form' => array_merge($gallery, $this->request->getPost()->all()),
                    'form_token' => $this->formTokenHelper->renderFormToken()
                ],
                $this->executeListPictures($id)
            );
        }
        throw new Core\Controller\Exception\ResultNotExistsException();
    }

    /**
     * @param int $id
     *
     * @return array
     */
    protected function executeListPictures($id)
    {
        $pictures = $this->pictureRepository->getPicturesByGalleryId($id);

        /** @var Core\Helpers\DataGrid $dataGrid */
        $dataGrid = $this->get('core.helpers.data_grid');
        $dataGrid
            ->setResults($pictures)
            ->setRecordsPerPage($this->user->getEntriesPerPage())
            ->setIdentifier('#acp-table')
            ->setResourcePathDelete('admin/gallery/pictures/delete/id_' . $id)
            ->setResourcePathEdit('admin/gallery/pictures/edit');

        $dataGrid
            ->addColumn([
                'label' => $this->translator->t('gallery', 'picture'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\PictureColumnRenderer::class,
                'fields' => ['id'],
                'custom' => [
                    'pattern' => 'gallery/index/image/id_%s/action_thumb',
                    'isRoute' => true
                ]
            ], 30)
            ->addColumn([
                'label' => $this->translator->t('system', 'description'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['description'],
            ], 20)
            ->addColumn([
                'label' => $this->translator->t('system', 'order'),
                'type' => Gallery\Helper\DataGrid\ColumnRenderer\PictureSortColumnRenderer::class,
                'fields' => ['pic'],
                'default_sort' => true
            ], 20)
            ->addColumn([
                'label' => $this->translator->t('system', 'id'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\IntegerColumnRenderer::class,
                'fields' => ['id'],
                'primary' => true
            ], 10);

        return [
            'grid' => $dataGrid->render(),
            'show_mass_delete_button' => $dataGrid->countDbResults() > 0
        ];
    }

    /**
     * @param array $formData
     * @param int   $galleryId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData, $galleryId)
    {
        return $this->actionHelper->handleEditPostAction(function () use ($formData, $galleryId) {
            $this->galleryFormValidation
                ->setUriAlias(sprintf(Gallery\Helpers::URL_KEY_PATTERN_GALLERY, $galleryId))
                ->validate($formData);

            $updateValues = [
                'start' => $this->date->toSQL($formData['start']),
                'end' => $this->date->toSQL($formData['end']),
                'title' => $this->get('core.helpers.secure')->strEncode($formData['title']),
                'user_id' => $this->user->getUserId(),
            ];

            $bool = $this->galleryRepository->update($updateValues, $galleryId);

            $this->insertUriAlias($formData, $galleryId);

            $this->generatePictureAliases($galleryId);

            $this->formTokenHelper->unsetFormToken();

            return $bool;
        });
    }

    /**
     * Setzt alle Bild-Aliase einer Fotogalerie neu
     *
     * @param integer $galleryId
     *
     * @return boolean
     */
    protected function generatePictureAliases($galleryId)
    {
        if ($this->aliases && $this->metaStatements && $this->uriAliasManager) {
            $pictures = $this->pictureRepository->getPicturesByGalleryId($galleryId);

            $alias = $this->aliases->getUriAlias(sprintf(Gallery\Helpers::URL_KEY_PATTERN_GALLERY, $galleryId), true);
            if (!empty($alias)) {
                $alias .= '/img';
            }
            $seoKeywords = $this->metaStatements->getKeywords(sprintf(Gallery\Helpers::URL_KEY_PATTERN_GALLERY, $galleryId));
            $seoDescription = $this->metaStatements->getDescription(sprintf(Gallery\Helpers::URL_KEY_PATTERN_GALLERY, $galleryId));

            foreach ($pictures as $picture) {
                $this->uriAliasManager->insertUriAlias(
                    sprintf(Gallery\Helpers::URL_KEY_PATTERN_PICTURE, $picture['id']),
                    !empty($alias) ? $alias . '-' . $picture['id'] : '',
                    $seoKeywords,
                    $seoDescription
                );
            }
        }

        return true;
    }
}
