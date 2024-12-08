<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Polls;

class Index extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Core\Date $date,
        private readonly Polls\Repository\PollRepository $pollRepository,
        private readonly Polls\ViewProviders\PollWidgetViewProvider $pollWidgetViewProvider,
        private readonly Polls\Helpers $pollHelpers,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): array
    {
        $poll = $this->pollRepository->getLatestPoll($this->date->getCurrentDateTime());

        if (!empty($poll) && $this->pollHelpers->hasAlreadyVoted($poll['id'])) {
            $this->setTemplate('Polls/Widget/index.result.tpl');
        } else {
            $this->setTemplate('Polls/Widget/index.vote.tpl');
        }

        return ($this->pollWidgetViewProvider)($poll);
    }
}
