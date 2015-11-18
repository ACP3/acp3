<?php

namespace ACP3\Modules\ACP3\Emoticons\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\ACP3\Emoticons;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Emoticons\Controller\Admin
 */
class Index extends Core\Modules\AdminController
{
    /**
     * @var \ACP3\Core\Helpers\FormToken
     */
    protected $formTokenHelper;
    /**
     * @var \ACP3\Modules\ACP3\Emoticons\Model\EmoticonRepository
     */
    protected $emoticonRepository;
    /**
     * @var \ACP3\Modules\ACP3\Emoticons\Validator
     */
    protected $emoticonsValidator;
    /**
     * @var \ACP3\Modules\ACP3\Emoticons\Cache
     */
    protected $emoticonsCache;

    /**
     * @param \ACP3\Core\Modules\Controller\AdminContext            $context
     * @param \ACP3\Core\Helpers\FormToken                          $formTokenHelper
     * @param \ACP3\Modules\ACP3\Emoticons\Model\EmoticonRepository $emoticonRepository
     * @param \ACP3\Modules\ACP3\Emoticons\Validator                $emoticonsValidator
     * @param \ACP3\Modules\ACP3\Emoticons\Cache                    $emoticonsCache
     */
    public function __construct(
        Core\Modules\Controller\AdminContext $context,
        Core\Helpers\FormToken $formTokenHelper,
        Emoticons\Model\EmoticonRepository $emoticonRepository,
        Emoticons\Validator $emoticonsValidator,
        Emoticons\Cache $emoticonsCache)
    {
        parent::__construct($context);

        $this->formTokenHelper = $formTokenHelper;
        $this->emoticonRepository = $emoticonRepository;
        $this->emoticonsValidator = $emoticonsValidator;
        $this->emoticonsCache = $emoticonsCache;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionCreate()
    {
        if ($this->request->getPost()->isEmpty() === false) {
            return $this->_createPost($this->request->getPost()->all());
        }

        $this->view->assign('form', array_merge(['code' => '', 'description' => ''], $this->request->getPost()->all()));

        $this->formTokenHelper->generateFormToken();
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function _createPost(array $formData)
    {
        return $this->actionHelper->handleCreatePostAction(function () use ($formData) {
            $file = $this->request->getFiles()->get('picture');

            $this->emoticonsValidator->validateCreate($formData, $file, $this->config->getSettings('emoticons'));

            $upload = new Core\Helpers\Upload('emoticons');
            $result = $upload->moveFile($file['tmp_name'], $file['name']);

            $insertValues = [
                'id' => '',
                'code' => Core\Functions::strEncode($formData['code']),
                'description' => Core\Functions::strEncode($formData['description']),
                'img' => $result['name'],
            ];

            $bool = $this->emoticonRepository->insert($insertValues);

            $this->emoticonsCache->saveCache();

            $this->formTokenHelper->unsetFormToken();

            return $bool;
        });
    }

    /**
     * @param string $action
     *
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionDelete($action = '')
    {
        return $this->actionHelper->handleDeleteAction(
            $this,
            $action,
            function ($items) {
                $bool = false;

                $upload = new Core\Helpers\Upload('emoticons');
                foreach ($items as $item) {
                    if (!empty($item) && $this->emoticonRepository->resultExists($item) === true) {
                        // Datei ebenfalls löschen
                        $file = $this->emoticonRepository->getOneImageById($item);
                        $upload->removeUploadedFile($file);
                        $bool = $this->emoticonRepository->delete($item);
                    }
                }

                $this->emoticonsCache->saveCache();

                return $bool;
            }
        );
    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \ACP3\Core\Exceptions\ResultNotExists
     */
    public function actionEdit($id)
    {
        $emoticon = $this->emoticonRepository->getOneById($id);

        if (empty($emoticon) === false) {
            if ($this->request->getPost()->isEmpty() === false) {
                return $this->_editPost($this->request->getPost()->all(), $emoticon, $id);
            }

            $this->view->assign('form', array_merge($emoticon, $this->request->getPost()->all()));

            $this->formTokenHelper->generateFormToken();
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    /**
     * @param array $formData
     * @param array $emoticon
     * @param int   $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function _editPost(array $formData, array $emoticon, $id)
    {
        return $this->actionHelper->handleEditPostAction(function () use ($formData, $emoticon, $id) {
            $file = $this->request->getFiles()->get('picture');

            $this->emoticonsValidator->validateEdit($formData, $file, $this->config->getSettings('emoticons'));

            $updateValues = [
                'code' => Core\Functions::strEncode($formData['code']),
                'description' => Core\Functions::strEncode($formData['description']),
            ];

            if (empty($file) === false) {
                $upload = new Core\Helpers\Upload('emoticons');
                $upload->removeUploadedFile($emoticon['img']);
                $result = $upload->moveFile($file['tmp_name'], $file['name']);
                $updateValues['img'] = $result['name'];
            }

            $bool = $this->emoticonRepository->update($updateValues, $id);

            $this->emoticonsCache->saveCache();

            $this->formTokenHelper->unsetFormToken();

            return $bool;
        });
    }

    public function actionIndex()
    {
        $emoticons = $this->emoticonRepository->getAll();

        /** @var Core\Helpers\DataGrid $dataGrid */
        $dataGrid = $this->get('core.helpers.data_grid');
        $dataGrid
            ->setResults($emoticons)
            ->setRecordsPerPage($this->user->getEntriesPerPage())
            ->setIdentifier('#acp-table')
            ->setResourcePathDelete('admin/emoticons/index/delete')
            ->setResourcePathEdit('admin/emoticons/index/edit');

        $dataGrid
            ->addColumn([
                'label' => $this->lang->t('system', 'description'),
                'type' => 'text',
                'fields' => ['description'],
            ], 40)
            ->addColumn([
                'label' => $this->lang->t('emoticons', 'code'),
                'type' => 'text',
                'fields' => ['code']
            ], 30)
            ->addColumn([
                'label' => $this->lang->t('emoticons', 'picture'),
                'type' => 'picture',
                'fields' => ['img'],
                'custom' => [
                    'pattern' => ROOT_DIR . 'uploads/emoticons/%s'
                ]
            ], 20)
            ->addColumn([
                'label' => $this->lang->t('system', 'id'),
                'type' => 'integer',
                'fields' => ['id'],
                'primary' => true,
                'default_sort' => true
            ], 10);

        return [
            'grid' => $dataGrid->render(),
            'show_mass_delete_button' => count($emoticons) > 0
        ];
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actionSettings()
    {
        if ($this->request->getPost()->isEmpty() === false) {
            return $this->_settingsPost($this->request->getPost()->all());
        }

        $this->view->assign('form', array_merge($this->config->getSettings('emoticons'), $this->request->getPost()->all()));

        $this->formTokenHelper->generateFormToken();
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function _settingsPost(array $formData)
    {
        return $this->actionHelper->handleSettingsPostAction(function () use ($formData) {
            $this->emoticonsValidator->validateSettings($formData);

            $data = [
                'width' => (int)$formData['width'],
                'height' => (int)$formData['height'],
                'filesize' => (int)$formData['filesize'],
            ];

            $this->formTokenHelper->unsetFormToken();

            return $this->config->setSettings($data, 'emoticons');
        });
    }
}
