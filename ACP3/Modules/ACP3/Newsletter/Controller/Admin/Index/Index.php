<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Newsletter;
use ACP3\Modules\ACP3\System\Installer\Schema;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Newsletter\Controller\Admin\Index
 */
class Index extends Core\Controller\AbstractAdminAction
{
    /**
     * @var \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterDataGridRepository
     */
    protected $dataGridRepository;

    /**
     * Index constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Modules\ACP3\Newsletter\Model\Repository\NewsletterDataGridRepository $dataGridRepository
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Newsletter\Model\Repository\NewsletterDataGridRepository $dataGridRepository
    ) {
        parent::__construct($context);

        $this->dataGridRepository = $dataGridRepository;
    }

    /**
     * @return array
     */
    public function execute()
    {
        /** @var Core\Helpers\DataGrid $dataGrid */
        $dataGrid = $this->get('core.helpers.data_grid');
        $dataGrid
            ->setRepository($this->dataGridRepository)
            ->setRecordsPerPage($this->resultsPerPage->getResultsPerPage(Schema::MODULE_NAME))
            ->setIdentifier('#newsletter-data-grid')
            ->setResourcePathEdit('admin/newsletter/index/edit')
            ->setResourcePathDelete('admin/newsletter/index/delete');

        $this->addDataGridColumns($dataGrid);

        return [
            'grid' => $dataGrid->render(),
            'show_mass_delete_button' => $dataGrid->countDbResults() > 0
        ];
    }

    /**
     * @param Core\Helpers\DataGrid $dataGrid
     */
    protected function addDataGridColumns(Core\Helpers\DataGrid $dataGrid)
    {
        $dataGrid
            ->addColumn([
                'label' => $this->translator->t('system', 'date'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\DateColumnRenderer::class,
                'fields' => ['date'],
                'default_sort' => true,
                'default_sort_direction' => 'desc'
            ], 50)
            ->addColumn([
                'label' => $this->translator->t('newsletter', 'subject'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['title'],
            ], 40)
            ->addColumn([
                'label' => $this->translator->t('newsletter', 'status'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\ReplaceValueColumnRenderer::class,
                'fields' => ['status'],
                'custom' => [
                    'search' => [0, 1],
                    'replace' => [
                        $this->translator->t('newsletter', 'not_yet_sent'),
                        $this->translator->t('newsletter', 'already_sent'),
                    ]
                ]
            ], 30)
            ->addColumn([
                'label' => $this->translator->t('system', 'id'),
                'type' => Core\Helpers\DataGrid\ColumnRenderer\IntegerColumnRenderer::class,
                'fields' => ['id'],
                'primary' => true
            ], 10);
    }
}
