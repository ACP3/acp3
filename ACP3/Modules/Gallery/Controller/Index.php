<?php

namespace ACP3\Modules\Gallery\Controller;

use ACP3\Core;
use ACP3\Modules\Gallery;

/**
 * Class Index
 * @package ACP3\Modules\Gallery\Controller
 */
class Index extends Core\Modules\Controller\Frontend
{
    /**
     * @var Core\Date
     */
    protected $date;
    /**
     * @var Core\Pagination
     */
    protected $pagination;
    /**
     * @var Gallery\Model
     */
    protected $galleryModel;
    /**
     * @var Gallery\Cache
     */
    protected $galleryCache;
    /**
     * @var Core\Config
     */
    protected $galleryConfig;

    public function __construct(
        Core\Context\Frontend $context,
        Core\Date $date,
        Core\Pagination $pagination,
        Gallery\Model $galleryModel,
        Gallery\Cache $galleryCache,
        Core\Config $galleryConfig)
    {
        parent::__construct($context);

        $this->date = $date;
        $this->pagination = $pagination;
        $this->galleryModel = $galleryModel;
        $this->galleryCache = $galleryCache;
        $this->galleryConfig = $galleryConfig;
    }

    public function actionDetails()
    {
        if ($this->galleryModel->pictureExists((int)$this->request->id, $this->date->getCurrentDateTime()) === true) {
            $picture = $this->galleryModel->getPictureById((int)$this->request->id);

            $settings = $this->galleryConfig->getSettings();

            // Brotkrümelspur
            $this->breadcrumb
                ->append($this->lang->t('gallery', 'gallery'), 'gallery')
                ->append($picture['title'], 'gallery/index/pics/id_' . $picture['gallery_id'])
                ->append($this->lang->t('gallery', 'details'))
                ->setTitlePrefix($picture['title'])
                ->setTitlePostfix(sprintf($this->lang->t('gallery', 'picture_x'), $picture['pic']));

            // Bildabmessungen berechnen
            $picture['width'] = $settings['width'];
            $picture['height'] = $settings['height'];
            $picInfos = @getimagesize(UPLOADS_DIR . 'gallery/' . $picture['file']);
            if ($picInfos !== false) {
                if ($picInfos[0] > $settings['width'] || $picInfos[1] > $settings['height']) {
                    if ($picInfos[0] > $picInfos[1]) {
                        $newWidth = $settings['width'];
                        $newHeight = intval($picInfos[1] * $newWidth / $picInfos[0]);
                    } else {
                        $newHeight = $settings['height'];
                        $newWidth = intval($picInfos[0] * $newHeight / $picInfos[1]);
                    }
                }

                $picture['width'] = isset($newWidth) ? $newWidth : $picInfos[0];
                $picture['height'] = isset($newHeight) ? $newHeight : $picInfos[1];
            }

            $this->view->assign('picture', $picture);

            // Vorheriges Bild
            $picture_back = $this->galleryModel->getPreviousPictureId($picture['pic'], $picture['gallery_id']);
            if (!empty($picture_back)) {
                $this->seo->setPreviousPage($this->router->route('gallery/index/details/id_' . $picture_back));
                $this->view->assign('picture_back', $picture_back);
            }

            // Nächstes Bild
            $picture_next = $this->galleryModel->getNextPictureId($picture['pic'], $picture['gallery_id']);
            if (!empty($picture_next)) {
                $this->seo->setNextPage($this->router->route('gallery/index/details/id_' . $picture_next));
                $this->view->assign('picture_next', $picture_next);
            }

            if ($settings['overlay'] == 0 && $settings['comments'] == 1 && $picture['comments'] == 1 && $this->modules->hasPermission('frontend/comments') === true) {
                $comments = $this->get('comments.controller.frontend.index');
                $comments
                    ->setModule('gallery')
                    ->setEntryId($this->request->id);

                $this->view->assign('comments', $comments->actionIndex());
            }
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionImage()
    {
        $this->setNoOutput(true);

        if ($this->get('core.validator.rules.misc')->isNumber($this->request->id) === true) {
            @set_time_limit(20);
            $picture = $this->galleryModel->getFileById($this->request->id);
            $action = $this->request->action === 'thumb' ? 'thumb' : '';

            $settings = $this->galleryConfig->getSettings();
            $options = array(
                'enable_cache' => CONFIG_CACHE_IMAGES == 1 ? true : false,
                'cache_prefix' => 'gallery_' . $action,
                'max_width' => $settings[$action . 'width'],
                'max_height' => $settings[$action . 'height'],
                'file' => UPLOADS_DIR . 'gallery/' . $picture,
                'prefer_height' => $action === 'thumb' ? true : false
            );

            $image = new Core\Image($options);
            $image->output();
        }
    }

    public function actionIndex()
    {
        $time = $this->date->getCurrentDateTime();

        $galleries = $this->galleryModel->getAll($time, POS, $this->auth->entries);
        $c_galleries = count($galleries);

        if ($c_galleries > 0) {
            $this->pagination->setTotalResults($this->galleryModel->countAll($time));
            $this->pagination->display();

            $settings = $this->galleryConfig->getSettings();

            for ($i = 0; $i < $c_galleries; ++$i) {
                $galleries[$i]['date_formatted'] = $this->date->format($galleries[$i]['start'], $settings['dateformat']);
                $galleries[$i]['date_iso'] = $this->date->format($galleries[$i]['start'], 'c');
                $galleries[$i]['pics_lang'] = $galleries[$i]['pics'] . ' ' . $this->lang->t('gallery', $galleries[$i]['pics'] == 1 ? 'picture' : 'pictures');
            }
            $this->view->assign('galleries', $galleries);
        }
    }

    public function actionPics()
    {
        if ($this->galleryModel->galleryExists((int)$this->request->id, $this->date->getCurrentDateTime()) === true) {
            // Cache der Galerie holen
            $pictures = $this->galleryCache->getCache($this->request->id);
            $c_pictures = count($pictures);

            $galleryTitle = $this->galleryModel->getGalleryTitle($this->request->id);

            // Brotkrümelspur
            $this->breadcrumb
                ->append($this->lang->t('gallery', 'gallery'), 'gallery')
                ->append($galleryTitle);

            if ($c_pictures > 0) {
                $settings = $this->galleryConfig->getSettings();

                for ($i = 0; $i < $c_pictures; ++$i) {
                    $pictures[$i]['uri'] = $this->router->route($settings['overlay'] == 1 ? 'gallery/index/image/id_' . $pictures[$i]['id'] . '/action_normal' : 'gallery/index/details/id_' . $pictures[$i]['id']);
                    $pictures[$i]['description'] = strip_tags($pictures[$i]['description']);
                }

                $this->view->assign('pictures', $pictures);
                $this->view->assign('overlay', (int)$settings['overlay']);
            }
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

}