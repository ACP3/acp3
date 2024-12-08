<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Core\Helpers\FormAction;
use ACP3\Modules\ACP3\Polls;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Symfony\Component\HttpFoundation\Response;

class VotePost extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly FormAction $actionHelper,
        private readonly Core\Date $date,
        private readonly Polls\Validation\VoteValidation $voteValidation,
        private readonly Polls\Model\VoteModel $voteModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>|string|Response
     *
     * @throws ConnectionException
     * @throws Exception
     */
    public function __invoke(int $id): array|string|Response
    {
        return $this->actionHelper->handlePostAction(
            function () use ($id) {
                $formData = $this->request->getPost()->all();
                $ipAddress = $this->request->getSymfonyRequest()->getClientIp();
                $time = $this->date->getCurrentDateTime();

                $this->voteValidation
                    ->withPollId($id)
                    ->withIpAddress($ipAddress)
                    ->validate($formData);

                $this->voteModel->vote($formData, $id, $ipAddress, $time);

                return $this->actionHelper->setRedirectMessage(true, $this->translator->t('polls', 'poll_success'), 'polls/index/result/id_' . $id);
            },
            'polls/index/vote/id_' . $id
        );
    }
}
