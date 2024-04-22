<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Guestbook\ViewProviders;

use ACP3\Core\Helpers\ResultsPerPage;
use ACP3\Core\Pagination;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Modules\ACP3\Guestbook\Installer\Schema as GuestbookSchema;
use ACP3\Modules\ACP3\Guestbook\Repository\GuestbookRepository;

class GuestbookListViewProvider
{
    public function __construct(private readonly GuestbookRepository $guestbookRepository, private readonly Pagination $pagination, private readonly ResultsPerPage $resultsPerPage, private readonly SettingsInterface $settings)
    {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(): array
    {
        $guestbookSettings = $this->settings->getSettings(GuestbookSchema::MODULE_NAME);

        $resultsPerPage = $this->resultsPerPage->getResultsPerPage(GuestbookSchema::MODULE_NAME);
        $this->pagination
            ->setResultsPerPage($resultsPerPage)
            ->setTotalResults($this->guestbookRepository->countAll($guestbookSettings['notify']));

        $guestbook = $this->guestbookRepository->getAll(
            $guestbookSettings['notify'],
            $this->pagination->getResultsStartOffset(),
            $resultsPerPage
        );

        return [
            'guestbook' => $guestbook,
            'overlay' => $guestbookSettings['overlay'],
            'pagination' => $this->pagination->render(),
            'dateformat' => $guestbookSettings['dateformat'],
        ];
    }
}
