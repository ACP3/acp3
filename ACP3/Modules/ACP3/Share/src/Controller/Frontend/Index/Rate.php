<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Share\Controller\Frontend\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\Context;
use ACP3\Modules\ACP3\Share\Model\ShareRatingModel;
use ACP3\Modules\ACP3\Share\Repository\ShareRatingsRepository;
use ACP3\Modules\ACP3\Share\Repository\ShareRepository;

class Rate extends AbstractWidgetAction
{
    private ?bool $alreadyRated = null;

    public function __construct(
        Context $context,
        private readonly ShareRepository $shareRepository,
        private readonly ShareRatingsRepository $shareRatingsRepository,
        private readonly ShareRatingModel $shareRatingModel,
    ) {
        parent::__construct($context);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function __invoke(int $id): array
    {
        $stars = (int) $this->request->getPost()->get('stars', 0);
        $ipAddress = $this->request->getSymfonyRequest()->getClientIp();

        if ($this->canSaveRating($id, $stars, $ipAddress) === true) {
            $this->shareRatingModel->save([
                'share_id' => $id,
                'stars' => $stars,
                'ip' => $ipAddress,
            ]);
        }

        return [
            'rating' => array_merge(
                $this->shareRatingsRepository->getRatingStatistics($id),
                [
                    'share_id' => $id,
                    'already_rated' => $this->hasAlreadyRated($ipAddress, $id),
                ]
            ),
        ];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function canSaveRating(int $shareId, int $stars, string $ipAddress): bool
    {
        if (!($stars >= 1 && $stars <= 5)) {
            return false;
        }
        if ($this->shareRepository->resultExistsById($shareId) === false) {
            return false;
        }
        if ($this->hasAlreadyRated($ipAddress, $shareId) === true) {
            return false;
        }

        return true;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function hasAlreadyRated(string $ipAddress, int $shareId): bool
    {
        if ($this->alreadyRated === null) {
            $this->alreadyRated = $this->shareRatingsRepository->hasAlreadyRated($ipAddress, $shareId);
        }

        return $this->alreadyRated;
    }
}
