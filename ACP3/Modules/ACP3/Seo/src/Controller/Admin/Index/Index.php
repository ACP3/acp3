<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Modules\ACP3\Seo;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Index extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Modules\ACP3\Seo\Model\Repository\DataGridRepository
     */
    private $dataGridRepository;
    /**
     * @var \ACP3\Core\SEO\MetaStatementsServiceInterface
     */
    private $metaStatements;
    /**
     * @var \ACP3\Core\DataGrid\DataGrid
     */
    private $dataGrid;

    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Seo\Model\Repository\DataGridRepository $dataGridRepository,
        MetaStatementsServiceInterface $metaStatements,
        Core\DataGrid\DataGrid $dataGrid
    ) {
        parent::__construct($context);

        $this->dataGridRepository = $dataGridRepository;
        $this->metaStatements = $metaStatements;
        $this->dataGrid = $dataGrid;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $input = (new Core\DataGrid\Input())
            ->setUseAjax(true)
            ->setRepository($this->dataGridRepository)
            ->setRecordsPerPage($this->resultsPerPage->getResultsPerPage(Schema::MODULE_NAME))
            ->setIdentifier('#seo-data-grid')
            ->setResourcePathDelete('admin/seo/index/delete')
            ->setResourcePathEdit('admin/seo/index/edit');

        $this->addDataGridColumns($input);

        return $this->dataGrid->render($input);
    }

    /**
     * @param \ACP3\Core\DataGrid\Input $input
     */
    protected function addDataGridColumns(Core\DataGrid\Input $input)
    {
        $input
            ->addColumn([
                'label' => $this->translator->t('seo', 'uri'),
                'type' => Core\DataGrid\ColumnRenderer\RouteColumnRenderer::class,
                'fields' => ['uri'],
                'default_sort' => true,
                'custom' => [
                    'path' => '%s',
                ],
            ], 60)
            ->addColumn([
                'label' => $this->translator->t('seo', 'alias'),
                'type' => Core\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['alias'],
            ], 50)
            ->addColumn([
                'label' => $this->translator->t('seo', 'keywords'),
                'type' => Core\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['keywords'],
            ], 40)
            ->addColumn([
                'label' => $this->translator->t('seo', 'description'),
                'type' => Core\DataGrid\ColumnRenderer\TextColumnRenderer::class,
                'fields' => ['description'],
            ], 30)
            ->addColumn([
                'label' => $this->translator->t('seo', 'robots'),
                'type' => Core\DataGrid\ColumnRenderer\ReplaceValueColumnRenderer::class,
                'fields' => ['robots'],
                'custom' => [
                    'search' => [0, 1, 2, 3, 4],
                    'replace' => [
                        $this->translator->t(
                            'seo',
                            'robots_use_system_default',
                            ['%default%' => $this->metaStatements->getRobotsSetting()]
                        ),
                        $this->translator->t('seo', 'robots_index_follow'),
                        $this->translator->t('seo', 'robots_index_nofollow'),
                        $this->translator->t('seo', 'robots_noindex_follow'),
                        $this->translator->t('seo', 'robots_noindex_nofollow'),
                    ],
                ],
            ], 20)
            ->addColumn([
                'label' => $this->translator->t('system', 'id'),
                'type' => Core\DataGrid\ColumnRenderer\IntegerColumnRenderer::class,
                'fields' => ['id'],
                'primary' => true,
            ], 10);
    }
}
