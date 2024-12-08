<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Assets\FileResolver;

use ACP3\Core\Authentication\Model\UserModelInterface;
use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Environment\AreaMatcher;
use Symfony\Component\HttpFoundation\RequestStack;

class MinifiedAwareFileCheckerStrategy implements FileCheckerStrategyInterface
{
    private ?AreaEnum $area = null;

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AreaMatcher $areaMatcher,
        private readonly UserModelInterface $userModel,
        private readonly StraightFileCheckerStrategy $straightFileCheckerStrategy,
    ) {
    }

    public function findResource(string $resourcePath): ?string
    {
        if (!$this->area) {
            $this->area = $this->areaMatcher->getAreaFromRequest($this->requestStack->getMainRequest());
        }

        if ($this->area === AreaEnum::AREA_ADMIN) {
            $result = $this->straightFileCheckerStrategy->findResource(
                preg_replace('=/([^/]+)\.(css|js)$=', '/admin-$1.min.$2', $resourcePath)
            );

            if ($result) {
                return $result;
            }
        }

        if ($this->userModel->isAuthenticated()) {
            $result = $this->straightFileCheckerStrategy->findResource(
                preg_replace('=/([^/]+)\.(css|js)$=', '/logged-in-$1.min.$2', $resourcePath)
            );

            if ($result) {
                return $result;
            }
        }

        return $this->straightFileCheckerStrategy->findResource(
            preg_replace('=/([^/]+)\.(css|js)$=', '/$1.min.$2', $resourcePath)
        );
    }

    public function isAllowed(string $resourcePath): bool
    {
        return (bool) preg_match('/.+(?<!\.min)\.(css|js)$/', $resourcePath);
    }
}
