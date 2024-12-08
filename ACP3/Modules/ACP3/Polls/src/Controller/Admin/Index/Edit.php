<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Polls\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Polls;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Core\Controller\Context\Context $context,
        private readonly Polls\Model\PollsModel $pollsModel,
        private readonly Polls\ViewProviders\AdminPollEditViewProvider $adminPollEditViewProvider,
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
        $poll = $this->pollsModel->getOneById($id);

        if (empty($poll) === false) {
            return ($this->adminPollEditViewProvider)($poll);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
