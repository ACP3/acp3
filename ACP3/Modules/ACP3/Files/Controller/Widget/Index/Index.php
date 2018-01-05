<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Files;

class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Modules\ACP3\Files\Model\Repository\FilesRepository
     */
    protected $filesRepository;

    /**
     * @param \ACP3\Core\Controller\Context\WidgetContext $context
     * @param \ACP3\Core\Date $date
     * @param \ACP3\Modules\ACP3\Files\Model\Repository\FilesRepository $filesRepository
     */
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Core\Date $date,
        Files\Model\Repository\FilesRepository $filesRepository
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->filesRepository = $filesRepository;
    }

    /**
     * @param int $categoryId
     * @param string $template
     *
     * @return array
     */
    public function execute(int $categoryId = 0, string $template = '')
    {
        $this->setCacheResponseCacheable();

        $settings = $this->config->getSettings(Files\Installer\Schema::MODULE_NAME);

        $this->view->setTemplate($template);

        return [
            'sidebar_files' => $this->fetchFiles($categoryId, $settings),
        ];
    }

    /**
     * @param int $categoryId
     * @param array $settings
     * @return array
     */
    private function fetchFiles(int $categoryId, array $settings)
    {
        if (!empty($categoryId)) {
            $files = $this->filesRepository->getAllByCategoryId(
                (int)$categoryId,
                $this->date->getCurrentDateTime(),
                $settings['sidebar']
            );
        } else {
            $files = $this->filesRepository->getAll($this->date->getCurrentDateTime(), $settings['sidebar']);
        }

        return $files;
    }
}
