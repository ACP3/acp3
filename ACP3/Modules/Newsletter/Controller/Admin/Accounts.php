<?php

namespace ACP3\Modules\Newsletter\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\Newsletter;

/**
 * Description of NewsletterAdmin
 *
 * @author Tino Goratsch
 */
class Accounts extends Core\Modules\Controller\Admin
{

    /**
     *
     * @var Newsletter\Model
     */
    protected $model;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->model = new Newsletter\Model($this->db);
    }

    public function actionActivate()
    {
        $bool = false;
        if ($this->get('core.validate')->isNumber($this->uri->id) === true) {
            $bool = $this->model->update(array('hash' => ''), $this->uri->id, Newsletter\Model::TABLE_NAME_ACCOUNTS);
        }

        $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
        $redirect->setMessage($bool, $this->lang->t('newsletter', $bool !== false ? 'activate_success' : 'activate_error'), 'acp/newsletter/accounts');
    }

    public function actionDelete()
    {
        $items = $this->_deleteItem('acp/newsletter/accounts/delete', 'acp/newsletter/accounts');

        if ($this->uri->action === 'confirmed') {
            $bool = false;
            foreach ($items as $item) {
                $bool = $this->model->delete($item, '', Newsletter\Model::TABLE_NAME_ACCOUNTS);
            }

            $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
            $redirect->setMessage($bool, $this->lang->t('system', $bool !== false ? 'delete_success' : 'delete_error'), 'acp/newsletter/accounts');
        } elseif (is_string($items)) {
            throw new Core\Exceptions\ResultNotExists();
        }
    }

    public function actionIndex()
    {
        $redirect = new Core\Helpers\RedirectMessages($this->uri, $this->view);
        $redirect->getMessage();

        $accounts = $this->model->getAllAccounts();
        $c_accounts = count($accounts);

        if ($c_accounts > 0) {
            $canDelete = $this->modules->hasPermission('admin/newsletter/accounts/delete');
            $config = array(
                'element' => '#acp-table',
                'sort_col' => $canDelete === true ? 3 : 2,
                'sort_dir' => 'desc',
                'hide_col_sort' => $canDelete === true ? 0 : ''
            );
            $this->appendContent($this->get('core.functions')->dataTable($config));

            $this->view->assign('accounts', $accounts);
            $this->view->assign('can_delete', $canDelete);
        }
    }

}