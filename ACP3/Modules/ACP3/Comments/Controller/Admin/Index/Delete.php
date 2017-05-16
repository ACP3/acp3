<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;

/**
 * Class Delete
 * @package ACP3\Modules\ACP3\Comments\Controller\Admin\Index
 */
class Delete extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Comments\Model\Repository\CommentsRepository
     */
    protected $commentRepository;

    /**
     * Delete constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Modules\ACP3\Comments\Model\Repository\CommentsRepository $commentRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Comments\Model\Repository\CommentsRepository $commentRepository
    ) {
        parent::__construct($context);

        $this->commentRepository = $commentRepository;
    }

    /**
     * @param string $action
     *
     * @return mixed
     * @throws \ACP3\Core\Controller\Exception\ResultNotExistsException
     */
    public function execute($action = '')
    {
        return $this->actionHelper->handleDeleteAction(
            $action,
            function (array $items) {
                $bool = false;
                foreach ($items as $item) {
                    $bool = $this->commentRepository->delete($item, 'module_id');
                }

                Core\Cache\Purge::doPurge($this->appPath->getCacheDir() . 'http');

                return $bool;
            }
        );
    }
}
