<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Files\Controller\Frontend\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Categories;
use ACP3\Modules\ACP3\Files\Installer\Schema;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Files\Controller\Frontend\Index
 */
class Index extends Core\Controller\AbstractFrontendAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Categories\Cache
     */
    protected $categoriesCache;

    /**
     * Index constructor.
     *
     * @param \ACP3\Core\Controller\Context\FrontendContext $context
     * @param \ACP3\Modules\ACP3\Categories\Cache           $categoriesCache
     */
    public function __construct(
        Core\Controller\Context\FrontendContext $context,
        Categories\Cache $categoriesCache
    ) {
        parent::__construct($context);

        $this->categoriesCache = $categoriesCache;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $this->setCacheResponseCacheable(
            $this->config->getSettings(\ACP3\Modules\ACP3\System\Installer\Schema::MODULE_NAME)['cache_lifetime']
        );

        return [
            'categories' => $this->categoriesCache->getCache(Schema::MODULE_NAME)
        ];
    }
}
