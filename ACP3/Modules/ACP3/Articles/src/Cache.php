<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Articles;

use ACP3\Core;
use ACP3\Modules\ACP3\Articles\Model\Repository\ArticleRepository;

class Cache extends Core\Modules\AbstractCacheStorage
{
    public const CACHE_ID = 'list_id_';

    /**
     * @var \ACP3\Modules\ACP3\Articles\Model\Repository\ArticleRepository
     */
    private $articleRepository;

    public function __construct(
        Core\Cache $cache,
        ArticleRepository $articleRepository
    ) {
        parent::__construct($cache);

        $this->articleRepository = $articleRepository;
    }

    public function getCache(int $articleId): array
    {
        if ($this->cache->contains(self::CACHE_ID . $articleId) === false) {
            $this->saveCache($articleId);
        }

        return $this->cache->fetch(self::CACHE_ID . $articleId);
    }

    public function saveCache(int $articleId): bool
    {
        return $this->cache->save(self::CACHE_ID . $articleId, $this->articleRepository->getOneById($articleId));
    }
}
