<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\ViewProviders;

use ACP3\Core\Breadcrumb\Steps;
use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Router\RouterInterface;
use ACP3\Core\SEO\MetaStatementsServiceInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Gallery\Helper\ThumbnailGenerator;
use ACP3\Modules\ACP3\Gallery\Helpers as GalleryHelpers;
use ACP3\Modules\ACP3\Gallery\Installer\Schema;
use ACP3\Modules\ACP3\Gallery\Repository\PictureRepository;

class GalleryPictureDetailsViewProvider
{
    public function __construct(private readonly MetaStatementsServiceInterface $metaStatements, private readonly PictureRepository $pictureRepository, private readonly RequestInterface $request, private readonly RouterInterface $router, private readonly SettingsInterface $settings, private readonly Steps $breadcrumb, private readonly ThumbnailGenerator $thumbnailGenerator, private readonly Title $title, private readonly Translator $translator)
    {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \ACP3\Core\Picture\Exception\PictureGenerateException
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $pictureId): array
    {
        $picture = $this->pictureRepository->getOneById($pictureId);

        $this->breadcrumb
            ->append($this->translator->t('gallery', 'gallery'), 'gallery')
            ->append($picture['gallery_title'], 'gallery/index/pics/id_' . $picture['gallery_id']);

        if (!empty($picture['title'])) {
            $this->breadcrumb->append(
                $picture['title'],
                $this->request->getQuery()
            );
        } else {
            $this->breadcrumb->append(
                $this->translator->t('gallery', 'picture_x', ['%picture%' => $picture['pic']]),
                $this->request->getQuery()
            );
        }

        $this->title->setPageTitlePostfix($picture['gallery_title']);

        $output = $this->thumbnailGenerator->generateThumbnail($picture['file'], '');
        $picture['file'] = $output->getFileWeb();
        $picture['width'] = $output->getWidth();
        $picture['height'] = $output->getHeight();

        $previousPicture = $this->pictureRepository->getPreviousPictureId($picture['pic'], $picture['gallery_id']);

        $nextPicture = $this->pictureRepository->getNextPictureId($picture['pic'], $picture['gallery_id']);

        $this->setSeoSettings($previousPicture, $nextPicture);

        return [
            'picture' => $picture,
            'picture_next' => $nextPicture,
            'picture_previous' => $previousPicture,
        ];
    }

    private function setSeoSettings(int $previousPicture, int $nextPicture): void
    {
        $settings = $this->settings->getSettings(Schema::MODULE_NAME);

        if (!empty($previousPicture)) {
            $this->metaStatements->setPreviousPage(
                $this->router->route(\sprintf(GalleryHelpers::URL_KEY_PATTERN_PICTURE, $previousPicture))
            );
        }
        if (!empty($nextPicture)) {
            $this->metaStatements->setNextPage(
                $this->router->route(\sprintf(GalleryHelpers::URL_KEY_PATTERN_PICTURE, $nextPicture))
            );
        }
    }
}
