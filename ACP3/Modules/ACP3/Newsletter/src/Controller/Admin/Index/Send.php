<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;
use Symfony\Component\HttpFoundation\Response;

class Send extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Core\Helpers\RedirectMessages $redirectMessages,
        private readonly Newsletter\Repository\NewsletterRepository $newsletterRepository,
        private readonly Newsletter\Repository\AccountRepository $accountRepository,
        private readonly Newsletter\Helper\SendNewsletter $newsletterHelpers,
    ) {
        parent::__construct($context);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): Response
    {
        if ($this->newsletterRepository->newsletterExists($id) === true) {
            $accounts = $this->accountRepository->getAllActiveAccounts();
            $recipients = [];

            foreach ($accounts as $i => $account) {
                $recipients[] = $accounts[$i]['mail'];
            }

            $sendNewsletterResult = $this->newsletterHelpers->sendNewsletter($id, $recipients);
            $newsletterUpdateResult = false;
            if ($sendNewsletterResult === true) {
                $newsletterUpdateResult = $this->newsletterRepository->update(['status' => '1'], $id);
            }

            return $this->redirectMessages->setMessage(
                $sendNewsletterResult === true && $newsletterUpdateResult !== false,
                $this->translator->t(
                    'newsletter',
                    $sendNewsletterResult === true && $newsletterUpdateResult !== false
                        ? 'create_success'
                        : 'create_save_error'
                )
            );
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
