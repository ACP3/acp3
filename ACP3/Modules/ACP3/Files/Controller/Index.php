<?php

namespace ACP3\Modules\ACP3\Files\Controller;

use ACP3\Core;
use ACP3\Core\Modules\FrontendController;
use ACP3\Modules\ACP3\Categories;
use ACP3\Modules\ACP3\Files;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;


/**
 * Class Index
 * @package ACP3\Modules\ACP3\Files\Controller
 */
class Index extends Core\Modules\FrontendController
{
    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\Files\Model
     */
    protected $filesModel;
    /**
     * @var \ACP3\Modules\ACP3\Files\Cache
     */
    protected $filesCache;
    /**
     * @var \ACP3\Modules\ACP3\Categories\Model
     */
    protected $categoriesModel;

    /**
     * @param \ACP3\Core\Modules\Controller\FrontendContext $context
     * @param \ACP3\Core\Date                               $date
     * @param \ACP3\Modules\ACP3\Files\Model                $filesModel
     * @param \ACP3\Modules\ACP3\Files\Cache                $filesCache
     * @param \ACP3\Modules\ACP3\Categories\Model           $categoriesModel
     */
    public function __construct(
        Core\Modules\Controller\FrontendContext $context,
        Core\Date $date,
        Files\Model $filesModel,
        Files\Cache $filesCache,
        Categories\Model $categoriesModel)
    {
        parent::__construct($context);

        $this->date = $date;
        $this->filesModel = $filesModel;
        $this->filesCache = $filesCache;
        $this->categoriesModel = $categoriesModel;
    }

    /**
     * @return array
     */
    public function actionIndex()
    {
        if ($this->modules->isActive('categories') === true) {
            $categories = $this->get('categories.cache')->getCache('files');
            if (count($categories) > 0) {
                return [
                    'categories' => $categories
                ];
            }
        }
    }

    /**
     * @param int    $id
     * @param string $action
     *
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionDetails($id, $action = '')
    {
        if ($this->filesModel->resultExists($id, $this->date->getCurrentDateTime()) === true) {
            $file = $this->filesCache->getCache($id);

            if ($action === 'download') {
                return $this->downloadFile($file);
            }

            $this->breadcrumb
                ->append($this->lang->t('files', 'files'), 'files')
                ->append($file['category_title'], 'files/index/files/cat_' . $file['category_id'])
                ->append($file['title']);

            $settings = $this->config->getSettings('files');

            return [
                'file' => $file,
                'dateformat' => $settings['dateformat'],
                'comments_allowed' => $settings['comments'] == 1 && $file['comments'] == 1
            ];
        }

        throw new Core\Exceptions\ResultNotExists();
    }

    /**
     * @param int $cat
     *
     * @return array
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionFiles($cat)
    {
        if ($this->get('core.validator.rules.misc')->isNumber($cat) && $this->categoriesModel->resultExists($cat) === true) {
            $category = $this->categoriesModel->getOneById($cat);

            $this->breadcrumb
                ->append($this->lang->t('files', 'files'), 'files')
                ->append($category['title']);

            $settings = $this->config->getSettings('files');

            return [
                'dateformat' => $settings['dateformat'],
                'files' => $this->filesModel->getAllByCategoryId($cat, $this->date->getCurrentDateTime())
            ];
        }

        throw new Core\Exceptions\ResultNotExists();
    }

    /**
     * @param $file
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    protected function downloadFile($file)
    {
        $path = UPLOADS_DIR . 'files/';
        if (is_file($path . $file['file'])) {
            $formatter = $this->get('core.helpers.stringFormatter');

            $ext = strrchr($file['file'], '.');
            $filename = $formatter->makeStringUrlSafe($file['title']) . $ext;

            $disposition = $this->response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
            $this->setContentType('application/force-download');
            $this->response->headers->add([
                'Content-Disposition' => $disposition,
                'Content-Transfer-Encoding' => 'binary',
                'Content-Length' => filesize($path . $file['file'])
            ]);

            return $this->response->setContent(file_get_contents($path . $file['file']));
        } elseif (preg_match('/^([a-z]+):\/\//', $file['file'])) { // External file
            return $this->redirect()->toNewPage($file['file']);
        }

        throw new Core\Exceptions\ResultNotExists();
    }
}
