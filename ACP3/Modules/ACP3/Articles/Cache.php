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
    const CACHE_ID = 'list_id_';

    /**
     * @var \ACP3\Modules\ACP3\Articles\Model\Repository\ArticleRepository
     */
    protected $articleRepository;

    /**
     * @param Core\Cache        $cache
     * @param ArticleRepository $articleRepository
     */
    public function __construct(
        Core\Cache $cache,
        ArticleRepository $articleRepository
    ) {
        parent::__construct($cache);

        $this->articleRepository = $articleRepository;
    }

    /**
     * @param int $articleId
     *
     * @return array
     */
    public function getCache($articleId)
    {
        if ($this->cache->contains(self::CACHE_ID . $articleId) === false) {
            $this->saveCache($articleId);
        }

        return $this->cache->fetch(self::CACHE_ID . $articleId);
    }

    /**
     * @param int $articleId
     *
     * @return bool
     */
    public function saveCache($articleId)
    {
        return $this->cache->save(self::CACHE_ID . $articleId, $this->articleRepository->getOneById($articleId));
    }
}
