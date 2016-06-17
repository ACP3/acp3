<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Comments;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Comments\Controller\Admin\Index
 */
class Index extends Core\Controller\AbstractAdminAction
{
    /**
     * @var \ACP3\Modules\ACP3\Comments\Model\CommentRepository
     */
    protected $commentRepository;

    /**
     * Index constructor.
     *
     * @param \ACP3\Core\Controller\Context\AdminContext          $context
     * @param \ACP3\Modules\ACP3\Comments\Model\CommentRepository $commentRepository
     */
    public function __construct(
        Core\Controller\Context\AdminContext $context,
        Comments\Model\CommentRepository $commentRepository)
    {
        parent::__construct($context);

        $this->commentRepository = $commentRepository;
    }

    /**
     * @return array
     */
    public function execute()
    {
        /** @var Core\Helpers\DataGrid $dataGrid */
        $dataGrid = $this->get('core.helpers.data_grid');
        $dataGrid
            ->setResults($this->commentRepository->getCommentsGroupedByModule())
            ->setRecordsPerPage($this->user->getEntriesPerPage())
            ->setIdentifier('#acp-table')
            ->setResourcePathDelete('admin/comments/index/delete')
            ->setResourcePathEdit('admin/comments/details/index');

        $dataGrid
            ->addColumn([
                'label' => $this->translator->t('comments', 'module'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\TranslateColumnRenderer::class,
                'fields' => ['module'],
                'default_sort' => true
            ], 30)
            ->addColumn([
                'label' => $this->translator->t('comments', 'comments_count'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\IntegerColumnRenderer::class,
                'fields' => ['comments_count'],
            ], 20)
            ->addColumn([
                'fields' => ['module_id'],
                'primary' => true
            ], 10);

        return [
            'grid' => $dataGrid->render(),
            'show_mass_delete_button' => $dataGrid->countDbResults() > 0
        ];
    }
}
