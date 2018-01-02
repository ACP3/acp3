<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Comments\Controller\Admin\Details;

use ACP3\Core;
use ACP3\Modules\ACP3\System;
use Symfony\Component\HttpFoundation\JsonResponse;

class Index extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var Core\View\Block\DataGridBlockInterface
     */
    private $block;

    /**
     * Index constructor.
     * @param Core\Controller\Context\FrontendContext $context
     * @param Core\View\Block\DataGridBlockInterface $block
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\View\Block\DataGridBlockInterface $block
    ) {
        parent::__construct($context);

        $this->block = $block;
    }

    /**
     * @param int $id
     *
     * @return array|JsonResponse
     */
    public function execute(int $id)
    {
        return $this->block
            ->setData([
                'moduleId' => $id
            ])
            ->render();
    }
}
