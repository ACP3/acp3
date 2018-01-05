<?php
namespace ACP3\Installer\Core\Http;

use ACP3\Core;

class Request extends Core\Http\Request
{
    /**
     * @inheritdoc
     */
    public function processQuery()
    {
        parent::processQuery();

        $this->symfonyRequest->attributes->set('_area', Core\Controller\AreaEnum::AREA_INSTALL);
    }
}
