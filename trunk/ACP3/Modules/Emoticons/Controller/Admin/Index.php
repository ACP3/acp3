<?php

namespace ACP3\Modules\Emoticons\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\Emoticons;

/**
 * Class Index
 * @package ACP3\Modules\Emoticons\Controller\Admin
 */
class Index extends Core\Modules\Controller\Admin
{
    /**
     * @var \ACP3\Core\Helpers\Secure
     */
    protected $secureHelper;
    /**
     * @var Emoticons\Model
     */
    protected $emoticonsModel;
    /**
     * @var \ACP3\Core\Config
     */
    protected $emoticonsConfig;
    /**
     * @var \ACP3\Modules\Emoticons\Cache
     */
    protected $emoticonsCache;

    public function __construct(
        Core\Context\Admin $context,
        Core\Helpers\Secure $secureHelper,
        Emoticons\Model $emoticonsModel,
        Core\Config $emoticonsConfig,
        Emoticons\Cache $emoticonsCache)
    {
        parent::__construct($context);

        $this->secureHelper = $secureHelper;
        $this->emoticonsModel = $emoticonsModel;
        $this->emoticonsConfig = $emoticonsConfig;
        $this->emoticonsCache = $emoticonsCache;
    }

    public function actionCreate()
    {
        if (empty($_POST) === false) {
            try {
                $file = array();
                if (!empty($_FILES['picture']['tmp_name'])) {
                    $file['tmp_name'] = $_FILES['picture']['tmp_name'];
                    $file['name'] = $_FILES['picture']['name'];
                    $file['size'] = $_FILES['picture']['size'];
                }

                $validator = $this->get('emoticons.validator');
                $validator->validateCreate($_POST, $file, $this->emoticonsConfig->getSettings());

                $upload = new Core\Helpers\Upload('emoticons');
                $result = $upload->moveFile($file['tmp_name'], $file['name']);

                $insertValues = array(
                    'id' => '',
                    'code' => Core\Functions::strEncode($_POST['code']),
                    'description' => Core\Functions::strEncode($_POST['description']),
                    'img' => $result['name'],
                );

                $bool = $this->emoticonsModel->insert($insertValues);

                $this->emoticonsCache->setCache();

                $this->secureHelper->unsetFormToken($this->request->query);

                $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'create_success' : 'create_error'), 'acp/emoticons');
            } catch (Core\Exceptions\InvalidFormToken $e) {
                $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/categories');
            } catch (Core\Exceptions\ValidationFailed $e) {
                $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
            }
        }

        $this->view->assign('form', array_merge(array('code' => '', 'description' => ''), $_POST));

        $this->secureHelper->generateFormToken($this->request->query);
    }

    public function actionDelete()
    {
        $items = $this->_deleteItem('acp/emoticons/index/delete', 'acp/emoticons');

        if ($this->request->action === 'confirmed') {
            $bool = false;

            $upload = new Core\Helpers\Upload('emoticons');
            foreach ($items as $item) {
                if (!empty($item) && $this->emoticonsModel->resultExists($item) === true) {
                    // Datei ebenfalls löschen
                    $file = $this->emoticonsModel->getOneImageById($item);
                    $upload->removeUploadedFile($file);
                    $bool = $this->emoticonsModel->delete($item);
                }
            }

            $this->emoticonsCache->setCache();

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'), 'acp/emoticons');
        } elseif (is_string($items)) {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionEdit()
    {
        $emoticon = $this->emoticonsModel->getOneById((int)$this->request->id);

        if (empty($emoticon) === false) {
            if (empty($_POST) === false) {
                try {
                    $file = array();
                    if (!empty($_FILES['picture']['name'])) {
                        $file['tmp_name'] = $_FILES['picture']['tmp_name'];
                        $file['name'] = $_FILES['picture']['name'];
                        $file['size'] = $_FILES['picture']['size'];
                    }

                    $validator = $this->get('emoticons.validator');
                    $validator->validateEdit($_POST, $file, $this->emoticonsConfig->getSettings());

                    $updateValues = array(
                        'code' => Core\Functions::strEncode($_POST['code']),
                        'description' => Core\Functions::strEncode($_POST['description']),
                    );

                    if (empty($file) === false) {
                        $upload = new Core\Helpers\Upload('emoticons');
                        $upload->removeUploadedFile($emoticon['img']);
                        $result = $upload->moveFile($file['tmp_name'], $file['name']);
                        $updateValues['img'] = $result['name'];
                    }

                    $bool = $this->emoticonsModel->update($updateValues, $this->request->id);

                    $this->emoticonsCache->setCache();

                    $this->secureHelper->unsetFormToken($this->request->query);

                    $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool !== false ? 'edit_success' : 'edit_error'), 'acp/emoticons');
                } catch (Core\Exceptions\InvalidFormToken $e) {
                    $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/news');
                } catch (Core\Exceptions\ValidationFailed $e) {
                    $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
                }
            }

            $this->view->assign('form', array_merge($emoticon, $_POST));

            $this->secureHelper->generateFormToken($this->request->query);
        } else {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionIndex()
    {
        $this->redirectMessages()->getMessage();

        $emoticons = $this->emoticonsModel->getAll();
        $c_emoticons = count($emoticons);

        if ($c_emoticons > 0) {
            $canDelete = $this->modules->hasPermission('admin/emoticons/index/delete');
            $config = array(
                'element' => '#acp-table',
                'sort_col' => $canDelete === true ? 4 : 3,
                'sort_dir' => 'desc',
                'hide_col_sort' => $canDelete === true ? 0 : ''
            );
            $this->view->assign('emoticons', $emoticons);
            $this->view->assign('can_delete', $canDelete);
            $this->appendContent($this->get('core.functions')->dataTable($config));
        }
    }

    public function actionSettings()
    {
        $config = $this->emoticonsConfig;

        if (empty($_POST) === false) {
            try {
                $validator = $this->get('emoticons.validator');
                $validator->validateSettings($_POST);

                $data = array(
                    'width' => (int)$_POST['width'],
                    'height' => (int)$_POST['height'],
                    'filesize' => (int)$_POST['filesize'],
                );
                $bool = $config->setSettings($data);

                $this->secureHelper->unsetFormToken($this->request->query);

                $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool === true ? 'settings_success' : 'settings_error'), 'acp/emoticons');
            } catch (Core\Exceptions\InvalidFormToken $e) {
                $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/emoticons');
            } catch (Core\Exceptions\ValidationFailed $e) {
                $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
            }
        }

        $this->view->assign('form', array_merge($config->getSettings(), $_POST));

        $this->secureHelper->generateFormToken($this->request->query);
    }

}
