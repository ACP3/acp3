<?php

namespace ACP3\Modules\Articles\Controller;

use ACP3\Core;
use ACP3\Modules\Articles;

/**
 * Module controller of the articles backend
 *
 * @author Tino Goratsch
 */
class Admin extends Core\Modules\Controller\Admin
{
    /**
     *
     * @var Articles\Model
     */
    protected $model;

    /**
     * @var \ACP3\Modules\Menus\Model
     */
    protected $menuModel;

    protected function _init()
    {
        $this->menuModel = new \ACP3\Modules\Menus\Model($this->db, $this->lang, $this->uri);
        $this->model = new Articles\Model($this->db, $this->lang, $this->menuModel, $this->uri);
    }

    public function actionCreate()
    {
        if (empty($_POST) === false) {
            try {
                $this->model->validateCreate($_POST);

                $insertValues = array(
                    'id' => '',
                    'start' => $this->date->toSQL($_POST['start']),
                    'end' => $this->date->toSQL($_POST['end']),
                    'title' => Core\Functions::strEncode($_POST['title']),
                    'text' => Core\Functions::strEncode($_POST['text'], true),
                    'user_id' => $this->auth->getUserId(),
                );

                $lastId = $this->model->insert($insertValues);
                if ((bool)CONFIG_SEO_ALIASES === true) {
                    $this->uri->insertUriAlias('articles/details/id_' . $lastId, $_POST['alias'], $_POST['seo_keywords'], $_POST['seo_description'], (int)$_POST['seo_robots']);
                    $this->seo->setCache();
                }

                if (isset($_POST['create']) === true && Core\Modules::hasPermission('menus', 'acp_create_item') === true) {
                    $insertValues = array(
                        'id' => '',
                        'mode' => 4,
                        'block_id' => $_POST['block_id'],
                        'parent_id' => (int)$_POST['parent'],
                        'display' => $_POST['display'],
                        'title' => Core\Functions::strEncode($_POST['title']),
                        'uri' => 'articles/details/id_' . $lastId . '/',
                        'target' => 1,
                    );

                    $nestedSet = new Core\NestedSet($this->db, \ACP3\Modules\Menus\Model::TABLE_NAME_ITEMS, true);
                    $lastId = $nestedSet->insertNode((int)$_POST['parent'], $insertValues);
                    $this->menuModel->setMenuItemsCache();
                }

                $this->session->unsetFormToken();

                Core\Functions::setRedirectMessage($lastId, $this->lang->t('system', $lastId !== false ? 'create_success' : 'create_error'), 'acp/articles');
            } catch (Core\Exceptions\InvalidFormToken $e) {
                Core\Functions::setRedirectMessage(false, $e->getMessage(), 'acp/articles');
            } catch (Core\Exceptions\ValidationFailed $e) {
                $this->view->assign('error_msg', $e->getMessage());
            }
        }

        if (Core\Modules::hasPermission('menus', 'acp_create_item') === true) {
            $lang_options = array($this->lang->t('articles', 'create_menu_item'));
            $this->view->assign('options', Core\Functions::selectGenerator('create', array(1), $lang_options, 0, 'checked'));

            // Block
            $this->view->assign('blocks', \ACP3\Modules\Menus\Helpers::menusDropdown());

            $lang_display = array($this->lang->t('system', 'yes'), $this->lang->t('system', 'no'));
            $this->view->assign('display', Core\Functions::selectGenerator('display', array(1, 0), $lang_display, 1, 'checked'));

            $this->view->assign('pages_list', \ACP3\Modules\Menus\Helpers::menuItemsList());
        }

        $this->view->assign('publication_period', $this->date->datepicker(array('start', 'end')));

        $defaults = array(
            'title' => '',
            'text' => '',
            'alias' => '',
            'seo_keywords' => '',
            'seo_description' => ''
        );

        $this->view->assign('SEO_FORM_FIELDS', $this->seo->formFields());

        $this->view->assign('form', array_merge($defaults, $_POST));

        $this->session->generateFormToken();
    }

    public function actionDelete()
    {
        $items = $this->_deleteItem('acp/articles/delete', 'acp/articles');

        if ($this->uri->action === 'confirmed') {
            $bool = false;

            $nestedSet = new Core\NestedSet($this->db, \ACP3\Modules\Menus\Model::TABLE_NAME_ITEMS, true);
            foreach ($items as $item) {
                $uri = 'articles/details/id_' . $item . '/';

                $bool = $this->model->delete($item);
                $nestedSet->deleteNode($this->menuModel->getMenuItemIdByUri($uri));

                Core\Cache::delete('list_id_' . $item, 'articles');
                $this->uri->deleteUriAlias($uri);
            }

            if (Core\Modules::isInstalled('menus') === true) {
                $this->menuModel->setMenuItemsCache();
            }

            $this->seo->setCache();

            Core\Functions::setRedirectMessage($bool, $this->lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'), 'acp/articles');
        } elseif (is_string($items)) {
            $this->uri->redirect('errors/404');
        }
    }

    public function actionEdit()
    {
        $article = $this->model->getOneById($this->uri->id);

        if (empty($article) === false) {
            if (empty($_POST) === false) {
                try {
                    $this->model->validateEdit($_POST);

                    $updateValues = array(
                        'start' => $this->date->toSQL($_POST['start']),
                        'end' => $this->date->toSQL($_POST['end']),
                        'title' => Core\Functions::strEncode($_POST['title']),
                        'text' => Core\Functions::strEncode($_POST['text'], true),
                        'user_id' => $this->auth->getUserId(),
                    );

                    $bool = $this->model->update($updateValues, $this->uri->id);

                    if ((bool)CONFIG_SEO_ALIASES === true) {
                        $this->uri->insertUriAlias('articles/details/id_' . $this->uri->id, $_POST['alias'], $_POST['seo_keywords'], $_POST['seo_description'], (int)$_POST['seo_robots']);
                        $this->seo->setCache();
                    }

                    $this->model->setCache($this->uri->id);

                    // Aliase in der Navigation aktualisieren
                    $this->menuModel->setMenuItemsCache();

                    $this->session->unsetFormToken();

                    Core\Functions::setRedirectMessage($bool, $this->lang->t('system', $bool !== false ? 'edit_success' : 'edit_error'), 'acp/articles');
                } catch (Core\Exceptions\InvalidFormToken $e) {
                    Core\Functions::setRedirectMessage(false, $e->getMessage(), 'acp/articles');
                } catch (Core\Exceptions\ValidationFailed $e) {
                    $this->view->assign('error_msg', $e->getMessage());
                }
            }

            // Datumsauswahl
            $this->view->assign('publication_period', $this->date->datepicker(array('start', 'end'), array($article['start'], $article['end'])));

            $this->view->assign('SEO_FORM_FIELDS', $this->seo->formFields('articles/details/id_' . $this->uri->id));

            $this->view->assign('form', array_merge($article, $_POST));

            $this->session->generateFormToken();
        } else {
            $this->uri->redirect('errors/404');
        }
    }

    public function actionList()
    {
        Core\Functions::getRedirectMessage();

        $articles = $this->model->getAllInAcp();
        $c_articles = count($articles);

        if ($c_articles > 0) {
            $can_delete = Core\Modules::hasPermission('articles', 'acp_delete');
            $config = array(
                'element' => '#acp-table',
                'sort_col' => $can_delete === true ? 2 : 1,
                'sort_dir' => 'asc',
                'hide_col_sort' => $can_delete === true ? 0 : ''
            );
            $this->view->appendContent(Core\Functions::dataTable($config));
            for ($i = 0; $i < $c_articles; ++$i) {
                $articles[$i]['period'] = $this->date->formatTimeRange($articles[$i]['start'], $articles[$i]['end']);
            }
            $this->view->assign('articles', $articles);
            $this->view->assign('can_delete', $can_delete);
        }
    }

}
