<?php

namespace ACP3\Modules\ACP3\Gallery\Controller;

use ACP3\Core;
use ACP3\Modules\ACP3\Gallery;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Gallery\Controller
 */
class Index extends Core\Modules\FrontendController
{
    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Pagination
     */
    protected $pagination;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Model\GalleryRepository
     */
    protected $galleryRepository;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Model\PictureRepository
     */
    protected $pictureRepository;
    /**
     * @var \ACP3\Modules\ACP3\Gallery\Cache
     */
    protected $galleryCache;
    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @param \ACP3\Core\Modules\Controller\FrontendContext      $context
     * @param \ACP3\Core\Date                                    $date
     * @param \ACP3\Core\Pagination                              $pagination
     * @param \ACP3\Modules\ACP3\Gallery\Model\GalleryRepository $galleryRepository
     * @param \ACP3\Modules\ACP3\Gallery\Model\PictureRepository $pictureRepository
     * @param \ACP3\Modules\ACP3\Gallery\Cache                   $galleryCache
     */
    public function __construct(
        Core\Modules\Controller\FrontendContext $context,
        Core\Date $date,
        Core\Pagination $pagination,
        Gallery\Model\GalleryRepository $galleryRepository,
        Gallery\Model\PictureRepository $pictureRepository,
        Gallery\Cache $galleryCache)
    {
        parent::__construct($context);

        $this->date = $date;
        $this->pagination = $pagination;
        $this->galleryRepository = $galleryRepository;
        $this->pictureRepository = $pictureRepository;
        $this->galleryCache = $galleryCache;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $this->settings = $this->config->getSettings('gallery');
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionDetails($id)
    {
        if ($this->pictureRepository->pictureExists($id, $this->date->getCurrentDateTime()) === true) {
            $picture = $this->pictureRepository->getPictureById($id);

            $this->breadcrumb
                ->append($this->translator->t('gallery', 'gallery'), 'gallery')
                ->append($picture['title'], 'gallery/index/pics/id_' . $picture['gallery_id'])
                ->append($this->translator->t('gallery', 'details'))
                ->setTitlePrefix($picture['title'])
                ->setTitlePostfix($this->translator->t('gallery', 'picture_x', ['%picture%' => $picture['pic']]));

            // Bildabmessungen berechnen
            $picture['width'] = $this->settings['width'];
            $picture['height'] = $this->settings['height'];
            $picInfos = @getimagesize($this->appPath->getUploadsDir() . 'gallery/' . $picture['file']);
            if ($picInfos !== false) {
                if ($picInfos[0] > $this->settings['width'] || $picInfos[1] > $this->settings['height']) {
                    if ($picInfos[0] > $picInfos[1]) {
                        $newWidth = $this->settings['width'];
                        $newHeight = intval($picInfos[1] * $newWidth / $picInfos[0]);
                    } else {
                        $newHeight = $this->settings['height'];
                        $newWidth = intval($picInfos[0] * $newHeight / $picInfos[1]);
                    }
                }

                $picture['width'] = isset($newWidth) ? $newWidth : $picInfos[0];
                $picture['height'] = isset($newHeight) ? $newHeight : $picInfos[1];
            }

            // Previous picture
            $previousPicture = $this->pictureRepository->getPreviousPictureId($picture['pic'], $picture['gallery_id']);
            if (!empty($previousPicture)) {
                $this->seo->setPreviousPage($this->router->route('gallery/index/details/id_' . $previousPicture));
                $this->view->assign('picture_back', $previousPicture);
            }

            // Next picture
            $nextPicture = $this->pictureRepository->getNextPictureId($picture['pic'], $picture['gallery_id']);
            if (!empty($nextPicture)) {
                $this->seo->setNextPage($this->router->route('gallery/index/details/id_' . $nextPicture));
                $this->view->assign('picture_next', $nextPicture);
            }

            return [
                'picture' => $picture,
                'comments_allowed' => $this->settings['overlay'] == 0 && $this->settings['comments'] == 1 && $picture['comments'] == 1
            ];
        }

        throw new Core\Exceptions\ResultNotExists();
    }

    /**
     * @param int    $id
     * @param string $action
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionImage($id, $action = '')
    {
        set_time_limit(20);
        $picture = $this->pictureRepository->getFileById($id);
        $action = $action === 'thumb' ? 'thumb' : '';

        /** @var Core\Picture $image */
        $image = $this->get('core.image');
        $image
            ->setEnableCache($this->config->getSettings('system')['cache_images'] == 1)
            ->setCachePrefix('gallery_' . $action)
            ->setMaxWidth($this->settings[$action . 'width'])
            ->setMaxHeight($this->settings[$action . 'height'])
            ->setFile($this->appPath->getUploadsDir() . 'gallery/' . $picture)
            ->setPreferHeight($action === 'thumb');

        if ($image->process()) {
            return $image->sendResponse();
        }

        throw new Core\Exceptions\ResultNotExists();
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        $time = $this->date->getCurrentDateTime();

        $this->pagination->setTotalResults($this->galleryRepository->countAll($time));
        $this->pagination->display();

        return [
            'galleries' => $this->galleryRepository->getAll($time, POS, $this->user->getEntriesPerPage()),
            'dateformat' => $this->settings['dateformat']
        ];
    }

    /**
     * @param int $id
     *
     * @return array
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionPics($id)
    {
        if ($this->galleryRepository->galleryExists($id, $this->date->getCurrentDateTime()) === true) {
            // Brotkrümelspur
            $this->breadcrumb
                ->append($this->translator->t('gallery', 'gallery'), 'gallery')
                ->append($this->galleryRepository->getGalleryTitle($id));

            return [
                'pictures' => $this->galleryCache->getCache($id),
                'overlay' => (int)$this->settings['overlay']
            ];
        }

        throw new Core\Exceptions\ResultNotExists();
    }
}
