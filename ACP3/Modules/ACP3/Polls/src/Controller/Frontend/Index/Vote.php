<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Polls;

class Vote extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Core\Date $date,
        private readonly Polls\Repository\PollRepository $pollRepository,
        private readonly Polls\ViewProviders\PollVoteViewProvider $pollVoteViewProvider,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        if ($this->pollRepository->pollExists($id, $this->date->getCurrentDateTime()) === true) {
            return ($this->pollVoteViewProvider)($id);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
