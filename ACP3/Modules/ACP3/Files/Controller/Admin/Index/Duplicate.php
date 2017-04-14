<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Admin\Index;


use ACP3\Core\Controller\AbstractAdminAction;
use ACP3\Core\Controller\Context\FrontendContext;
use ACP3\Modules\ACP3\Files\Model\FilesModel;

class Duplicate extends AbstractAdminAction
{
    /**
     * @var FilesModel
     */
    private $filesModel;

    /**
     * Duplicate constructor.
     * @param FrontendContext $context
     * @param FilesModel $filesModel
     */
    public function __construct(
        FrontendContext $context,
        FilesModel $filesModel
    ) {
        parent::__construct($context);

        $this->filesModel = $filesModel;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function execute($id)
    {
        $result = $this->filesModel->duplicate($id);

        return $this->redirectMessages()->setMessage(
            $result,
            $this->translator->t('system', $result !== false ? 'duplicate_success' : 'duplicate_error')
        );
    }
}