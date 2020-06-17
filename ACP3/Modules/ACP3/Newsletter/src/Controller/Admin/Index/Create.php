<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Modules\ACP3\Newsletter;

class Create extends AbstractFormAction
{
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Validation\AdminFormValidation
     */
    private $adminFormValidation;
    /**
     * @var Newsletter\Model\NewsletterModel
     */
    private $newsletterModel;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\ViewProviders\AdminNewsletterEditViewProvider
     */
    private $adminNewsletterEditViewProvider;
    /**
     * @var \ACP3\Core\Authentication\Model\UserModelInterface
     */
    private $user;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        UserModelInterface $user,
        Newsletter\Model\NewsletterModel $newsletterModel,
        Newsletter\Validation\AdminFormValidation $adminFormValidation,
        Newsletter\Helper\SendNewsletter $newsletterHelpers,
        Newsletter\ViewProviders\AdminNewsletterEditViewProvider $adminNewsletterEditViewProvider
    ) {
        parent::__construct($context, $newsletterHelpers);

        $this->newsletterModel = $newsletterModel;
        $this->adminFormValidation = $adminFormValidation;
        $this->adminNewsletterEditViewProvider = $adminNewsletterEditViewProvider;
        $this->user = $user;
    }

    public function execute(): array
    {
        $defaults = [
            'action' => 1,
            'title' => '',
            'test' => 0,
            'text' => '',
            'date' => '',
        ];

        return ($this->adminNewsletterEditViewProvider)($defaults);
    }

    /**
     * @return array|string|\Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function executePost()
    {
        return $this->actionHelper->handlePostAction(function () {
            $formData = $this->request->getPost()->all();

            $settings = $this->config->getSettings(Newsletter\Installer\Schema::MODULE_NAME);

            $this->adminFormValidation->validate($formData);

            $formData['user_id'] = $this->user->getUserId();
            $newsletterId = $this->newsletterModel->save($formData);

            [$text, $result] = $this->sendTestNewsletter(
                $formData['test'] == 1,
                $newsletterId,
                $newsletterId,
                $settings['mail']
            );

            return $this->redirectMessages()->setMessage($result, $text);
        });
    }
}
