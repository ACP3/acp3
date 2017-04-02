<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Search\Controller\Widget\Index;

use ACP3\Core;
use ACP3\Modules\ACP3\Search;
use ACP3\Modules\ACP3\System\Installer\Schema;

/**
 * Class Index
 * @package ACP3\Modules\ACP3\Search\Controller\Widget\Index
 */
class Index extends Core\Controller\AbstractWidgetAction
{
    use Core\Cache\CacheResponseTrait;

    /**
     * @var \ACP3\Modules\ACP3\Search\Helpers
     */
    protected $searchHelpers;

    /**
     * @param \ACP3\Core\Controller\Context\WidgetContext $context
     * @param \ACP3\Modules\ACP3\Search\Helpers           $searchHelpers
     */
    public function __construct(
        Core\Controller\Context\WidgetContext $context,
        Search\Helpers $searchHelpers)
    {
        parent::__construct($context);

        $this->searchHelpers = $searchHelpers;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $this->setCacheResponseCacheable($this->config->getSettings(Schema::MODULE_NAME)['cache_lifetime']);

        return [
            'search_mods' => $this->searchHelpers->getModules()
        ];
    }
}
