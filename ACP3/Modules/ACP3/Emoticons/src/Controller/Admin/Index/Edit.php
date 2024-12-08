<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Emoticons\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Emoticons;

class Edit extends Core\Controller\AbstractWidgetAction
{
    public function __construct(
        Context $context,
        private readonly Emoticons\Model\EmoticonsModel $emoticonsModel,
        private readonly Emoticons\ViewProviders\AdminEmoticonEditViewProvider $adminEmoticonEditViewProvider,
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
        $emoticon = $this->emoticonsModel->getOneById($id);

        if (empty($emoticon) === false) {
            return ($this->adminEmoticonEditViewProvider)($emoticon);
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
