<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers. See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Guestbook;
use ACP3\Modules\ACP3\Newsletter;
use ACP3\Modules\ACP3\Seo\Installer\Schema;

/**
 * Class Create
 * @package ACP3\Modules\ACP3\Guestbook\Controller\Frontend\Index
 */
class Create extends AbstractAction
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
     * @var \ACP3\Modules\ACP3\Guestbook\Model\Repository\GuestbookRepository
     */
    protected $guestbookRepository;
    /**
     * @var \ACP3\Modules\ACP3\Guestbook\Validation\FormValidation
     */
    protected $formValidation;
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Helper\Subscribe
     */
    protected $newsletterSubscribeHelper;
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    protected $formsHelper;
    /**
     * @var bool
     */
    protected $newsletterActive = false;

    /**
     * Create constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Core\Date $date
     * @param \ACP3\Core\Helpers\Forms $formsHelper
     * @param \ACP3\Core\Helpers\FormToken $formTokenHelper
     * @param \ACP3\Modules\ACP3\Guestbook\Model\Repository\GuestbookRepository $guestbookRepository
     * @param \ACP3\Modules\ACP3\Guestbook\Validation\FormValidation $formValidation
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        Core\Helpers\Forms $formsHelper,
        Core\Helpers\FormToken $formTokenHelper,
        Guestbook\Model\Repository\GuestbookRepository $guestbookRepository,
        Guestbook\Validation\FormValidation $formValidation
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->formsHelper = $formsHelper;
        $this->formTokenHelper = $formTokenHelper;
        $this->guestbookRepository = $guestbookRepository;
        $this->formValidation = $formValidation;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $this->newsletterActive = ($this->guestbookSettings['newsletter_integration'] == 1);
    }

    /**
     * @param \ACP3\Modules\ACP3\Newsletter\Helper\Subscribe $newsletterSubscribeHelper
     *
     * @return $this
     */
    public function setNewsletterSubscribeHelper(Newsletter\Helper\Subscribe $newsletterSubscribeHelper)
    {
        $this->newsletterSubscribeHelper = $newsletterSubscribeHelper;

        return $this;
    }

    /**
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute()
    {
        if ($this->request->getPost()->count() !== 0) {
            return $this->executePost($this->request->getPost()->all());
        }

        if ($this->newsletterActive === true && $this->newsletterSubscribeHelper) {
            $newsletterSubscription = [
                1 => $this->translator->t(
                    'guestbook',
                    'subscribe_to_newsletter',
                    ['%title%' => $this->config->getSettings(Schema::MODULE_NAME)['title']]
                )
            ];
            $this->view->assign(
                'subscribe_newsletter',
                $this->formsHelper->checkboxGenerator('subscribe_newsletter', $newsletterSubscription, '1')
            );
        }

        return [
            'form' => array_merge($this->fetchFormDefaults(), $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken(),
            'can_use_emoticons' => $this->guestbookSettings['emoticons'] == 1
        ];
    }

    /**
     * @param array $formData
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executePost(array $formData)
    {
        return $this->actionHelper->handlePostAction(
            function () use ($formData) {
                $ipAddress = $this->request->getSymfonyRequest()->getClientIp();

                $this->formValidation
                    ->setIpAddress($ipAddress)
                    ->setNewsletterAccess($this->newsletterActive)
                    ->validate($formData);

                $insertValues = [
                    'id' => '',
                    'date' => $this->date->toSQL(),
                    'ip' => $ipAddress,
                    'name' => $this->get('core.helpers.secure')->strEncode($formData['name']),
                    'user_id' => $this->user->isAuthenticated() ? $this->user->getUserId() : null,
                    'message' => $this->get('core.helpers.secure')->strEncode($formData['message']),
                    'website' => $this->get('core.helpers.secure')->strEncode($formData['website']),
                    'mail' => $formData['mail'],
                    'active' => $this->guestbookSettings['notify'] == 2 ? 0 : 1,
                ];

                $lastId = $this->guestbookRepository->insert($insertValues);

                if ($this->guestbookSettings['notify'] == 1 || $this->guestbookSettings['notify'] == 2) {
                    $this->sendNotificationEmail($lastId);
                }

                // Falls es der Benutzer ausgewählt hat, diesen in den Newsletter eintragen
                if ($this->newsletterActive === true &&
                    $this->newsletterSubscribeHelper &&
                    isset($formData['subscribe_newsletter']) &&
                    $formData['subscribe_newsletter'] == 1
                ) {
                    $this->newsletterSubscribeHelper->subscribeToNewsletter($formData['mail']);
                }

                Core\Cache\Purge::doPurge($this->appPath->getCacheDir() . 'http');

                return $this->redirectMessages()->setMessage(
                    $lastId,
                    $this->translator->t('system', $lastId !== false ? 'create_success' : 'create_error')
                );
            }
        );
    }

    /**
     * @param int $entryId
     */
    protected function sendNotificationEmail($entryId)
    {
        $fullPath = $this->router->route('guestbook', true) . '#gb-entry-' . $entryId;
        $body = sprintf(
            $this->guestbookSettings['notify'] == 1
                ? $this->translator->t('guestbook', 'notification_email_body_1')
                : $this->translator->t('guestbook', 'notification_email_body_2'),
            $this->router->route('', true),
            $fullPath
        );
        $this->get('core.helpers.sendEmail')->execute(
            '',
            $this->guestbookSettings['notify_email'],
            $this->guestbookSettings['notify_email'],
            $this->translator->t('guestbook', 'notification_email_subject'),
            $body
        );
    }

    /**
     * @return array
     */
    private function fetchFormDefaults()
    {
        $defaults = [
            'name' => '',
            'name_disabled' => false,
            'mail' => '',
            'mail_disabled' => false,
            'website' => '',
            'website_disabled' => false,
            'message' => '',
        ];

        if ($this->user->isAuthenticated() === true) {
            $users = $this->user->getUserInfo();
            $defaults['name'] = $users['nickname'];
            $defaults['name_disabled'] = true;
            $defaults['mail'] = $users['mail'];
            $defaults['mail_disabled'] = true;
            $defaults['website'] = $users['website'];
            $defaults['website_disabled'] = !empty($users['website']);
        }
        return $defaults;
    }
}
