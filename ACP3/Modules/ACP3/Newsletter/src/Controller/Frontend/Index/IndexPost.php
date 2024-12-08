<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Environment\ApplicationPath;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Newsletter;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class IndexPost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly ApplicationPath $applicationPath,
        private readonly FormAction $actionHelper,
        private readonly Core\Helpers\Alerts $alertsHelper,
        private readonly Newsletter\Helper\Subscribe $subscribeHelper,
        private readonly Newsletter\Validation\SubscribeFormValidation $subscribeFormValidation,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(): array|string|Response
    {
        return $this->actionHelper->handlePostAction(
            function () {
                $formData = $this->request->getPost()->all();

                $this->subscribeFormValidation->validate($formData);

                $result = $this->subscribeHelper->subscribeToNewsletter(
                    $formData['mail'],
                    $formData['salutation'],
                    $formData['first_name'],
                    $formData['last_name']
                );

                return $this->alertsHelper->confirmBox(
                    $this->translator->t('newsletter', $result !== false ? 'subscribe_success' : 'subscribe_error'),
                    $this->applicationPath->getWebRoot()
                );
            }
        );
    }
}
