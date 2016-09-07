<?php
/**
 * Copyright (c) 2016 by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Model;


use ACP3\Core\Helpers\Secure;
use ACP3\Core\Model\AbstractModel;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Gallery\Installer\Schema;
use ACP3\Modules\ACP3\Gallery\Model\Repository\PictureRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PictureModel extends AbstractModel
{
    const EVENT_PREFIX = Schema::MODULE_NAME;

    /**
     * @var Secure
     */
    protected $secure;
    /**
     * @var PictureRepository
     */
    protected $repository;
    /**
     * @var SettingsInterface
     */
    protected $config;

    /**
     * PictureModel constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param SettingsInterface $config
     * @param Secure $secure
     * @param PictureRepository $pictureRepository
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        SettingsInterface $config,
        Secure $secure,
        PictureRepository $pictureRepository
    ) {
        parent::__construct($eventDispatcher, $pictureRepository);

        $this->secure = $secure;
        $this->config = $config;
    }

    /**
     * @param array $formData
     * @param int $galleryId
     * @param int|null $entryId
     * @return bool|int
     */
    public function savePicture(array $formData, $galleryId, $entryId = null)
    {
        $settings = $this->config->getSettings(Schema::MODULE_NAME);

        $data = [
            'gallery_id' => $galleryId,
            'description' => $this->secure->strEncode($formData['description'], true),
            'comments' => $settings['comments'] == 1
                ? (isset($formData['comments']) && $formData['comments'] == 1 ? 1 : 0)
                : $settings['comments'],
        ];

        if (isset($formData['file'])) {
            $data['file'] = $formData['file'];
        }
        if ($entryId === null) {
            $picNum = $this->repository->getLastPictureByGalleryId($entryId);
            $data['pic'] = !is_null($picNum) ? $picNum + 1 : 1;
        }

        return $this->save($data, $entryId);
    }
}
