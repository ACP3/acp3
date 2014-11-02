<?php

namespace ACP3\Modules\Contact\Controller\Admin;

use ACP3\Core;
use ACP3\Modules\Contact;

/**
 * Class Index
 * @package ACP3\Modules\Contact\Controller\Admin
 */
class Index extends Core\Modules\Controller\Admin
{
    /**
     * @var \ACP3\Core\Config
     */
    protected $contactConfig;

    public function __construct(
        Core\Context\Admin $context,
        Core\Helpers\Secure $secureHelper,
        Core\Config $contactConfig)
    {
        parent::__construct($context);

        $this->secureHelper = $secureHelper;
        $this->contactConfig = $contactConfig;
    }

    public function actionIndex()
    {
        if (empty($_POST) === false) {
            $this->_indexPost($_POST);
        }

        $settings = $this->contactConfig->getSettings();

        $this->view->assign('form', array_merge($settings, $_POST));

        $this->secureHelper->generateFormToken($this->request->query);
    }

    private function _indexPost(array $formData)
    {
        try {
            $validator = $this->get('contact.validator');
            $validator->validateSettings($formData);

            $data = array(
                'address' => Core\Functions::strEncode($formData['address'], true),
                'mail' => $formData['mail'],
                'telephone' => Core\Functions::strEncode($formData['telephone']),
                'fax' => Core\Functions::strEncode($formData['fax']),
                'disclaimer' => Core\Functions::strEncode($formData['disclaimer'], true),
            );

            $bool = $this->contactConfig->setSettings($data);

            $this->secureHelper->unsetFormToken($this->request->query);

            $this->redirectMessages()->setMessage($bool, $this->lang->t('system', $bool === true ? 'settings_success' : 'settings_error'), 'acp/contact');
        } catch (Core\Exceptions\InvalidFormToken $e) {
            $this->redirectMessages()->setMessage(false, $e->getMessage(), 'acp/contact');
        } catch (Core\Exceptions\ValidationFailed $e) {
            $this->view->assign('error_msg', $this->get('core.helpers.alerts')->errorBox($e->getMessage()));
        }
    }

}