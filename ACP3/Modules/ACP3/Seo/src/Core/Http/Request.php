<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Core\Http;

use ACP3\Core\Controller\AreaEnum;
use ACP3\Core\Environment\AreaMatcher;
use ACP3\Core\Http\Request as BaseRequest;
use ACP3\Modules\ACP3\Seo\Repository\SeoRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class Request extends BaseRequest
{
    public function __construct(
        RequestStack $requestStack,
        AreaMatcher $areaMatcher,
        private readonly SeoRepository $seoRepository,
    ) {
        parent::__construct($requestStack, $areaMatcher);
    }

    protected function parseURI(): void
    {
        if ($this->getArea() === AreaEnum::AREA_FRONTEND) {
            $this->checkForUriAlias();
        }

        parent::parseURI();
    }

    /**
     * Checks, whether the current request may equal an uri alias.
     */
    protected function checkForUriAlias(): void
    {
        [$params, $probableQuery] = $this->checkUriAliasForAdditionalParameters();

        // Nachschauen, ob ein URI-Alias für die aktuelle Seite festgelegt wurde
        $alias = $this->seoRepository->getUriByAlias(substr($probableQuery, 0, -1));
        if (!empty($alias)) {
            $this->query = $alias . $params;
        }
    }

    /**
     * Annehmen, dass ein URI Alias mit zusätzlichen Parametern übergeben wurde.
     *
     * @return string[]
     */
    protected function checkUriAliasForAdditionalParameters(): array
    {
        $params = '';
        $probableQuery = $this->query;
        if (preg_match('/^([a-z]{1}[a-z\d\-]*\/)([a-z\d\-]+\/)*(([a-z\d\-]+)_(.+)\/)+$/', $this->query)) {
            $query = preg_split('=/=', $this->query, -1, PREG_SPLIT_NO_EMPTY);
            if (isset($query[1]) === false) {
                $query[1] = 'index';
            }
            if (isset($query[2]) === false) {
                $query[2] = 'index';
            }

            $length = 0;
            foreach ($query as $row) {
                if (str_contains((string) $row, '_')) {
                    break;
                }

                $length += \strlen((string) $row) + 1;
            }
            $params = substr($this->query, $length);
            $probableQuery = substr($this->query, 0, $length);
        }

        return [$params, $probableQuery];
    }
}
