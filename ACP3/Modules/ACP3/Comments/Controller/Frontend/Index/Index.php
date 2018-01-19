<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Index extends AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Core\Pagination
     */
    protected $pagination;
    /**
     * @var \ACP3\Modules\ACP3\Comments\Model\Repository\CommentRepository
     */
    protected $commentRepository;

    /**
     * @param \ACP3\Core\Controller\Context\FrontendContext                  $context
     * @param \ACP3\Core\Pagination                                          $pagination
     * @param \ACP3\Modules\ACP3\Comments\Model\Repository\CommentRepository $commentRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Pagination $pagination,
        Comments\Model\Repository\CommentRepository $commentRepository
    ) {
        parent::__construct($context);

        $this->pagination = $pagination;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @param string $module
     * @param int    $entryId
     *
     * @return array
     */
    public function execute($module, $entryId)
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        $resultsPerPage = $this->resultsPerPage->getResultsPerPage(Comments\Installer\Schema::MODULE_NAME);
        $this->pagination
            ->setResultsPerPage($resultsPerPage)
            ->setTotalResults(
                $this->commentRepository->countAllByModule($this->modules->getModuleId($module), $entryId)
            );

        $comments = $this->commentRepository->getAllByModule(
            $this->modules->getModuleId($module),
            $entryId,
            $this->pagination->getResultsStartOffset(),
            $resultsPerPage
        );
        $cComments = \count($comments);

        for ($i = 0; $i < $cComments; ++$i) {
            if (empty($comments[$i]['name'])) {
                $comments[$i]['name'] = $this->translator->t('users', 'deleted_user');
            }
            if ($this->emoticonsActive === true && $this->emoticonsHelpers) {
                $comments[$i]['message'] = $this->emoticonsHelpers->emoticonsReplace($comments[$i]['message']);
            }
        }

        return [
            'comments' => $comments,
            'dateformat' => $this->commentsSettings['dateformat'],
            'pagination' => $this->pagination->render(),
        ];
    }
}
