<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Files;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class Download
 * @package ACP3\Modules\ACP3\Files\Controller\Frontend\Index
 */
class Download extends Core\Controller\AbstractFrontendAction
{
    /**
     * @var \ACP3\Core\Date
     */
    protected $date;
    /**
     * @var \ACP3\Core\Helpers\StringFormatter
     */
    protected $stringFormatter;
    /**
     * @var \ACP3\Modules\ACP3\Files\Model\Repository\FilesRepository
     */
    protected $filesRepository;
    /**
     * @var \ACP3\Modules\ACP3\Files\Cache\FileCacheStorage
     */
    protected $filesCache;

    /**
     * @param \ACP3\Core\Controller\Context\FrontendContext  $context
     * @param \ACP3\Core\Date                                $date
     * @param \ACP3\Core\Helpers\StringFormatter             $stringFormatter
     * @param \ACP3\Modules\ACP3\Files\Model\Repository\FilesRepository $filesRepository
     * @param \ACP3\Modules\ACP3\Files\Cache\FileCacheStorage                 $filesCache
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Core\Date $date,
        Core\Helpers\StringFormatter $stringFormatter,
        Files\Model\Repository\FilesRepository $filesRepository,
        Files\Cache\FileCacheStorage $filesCache
    ) {
        parent::__construct($context);

        $this->date = $date;
        $this->stringFormatter = $stringFormatter;
        $this->filesRepository = $filesRepository;
        $this->filesCache = $filesCache;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Core\Controller\Exception\ResultNotExistsException
     */
    public function execute(int $id)
    {
        if ($this->filesRepository->resultExists($id, $this->date->getCurrentDateTime()) === true) {
            $file = $this->filesCache->getCache($id);

            $path = $this->appPath->getUploadsDir() . 'files/';
            if (is_file($path . $file['file'])) {
                $ext = strrchr($file['file'], '.');
                $filename = $this->stringFormatter->makeStringUrlSafe($file['title']) . $ext;

                $response = new BinaryFileResponse($path . $file['file']);
                $response->setContentDisposition(
                    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                    $filename
                );

                return $response;
            } elseif (preg_match('/^([a-z]+):\/\//', $file['file'])) { // External file
                return $this->redirect()->toNewPage($file['file']);
            }
        }

        throw new Core\Controller\Exception\ResultNotExistsException();
    }
}
