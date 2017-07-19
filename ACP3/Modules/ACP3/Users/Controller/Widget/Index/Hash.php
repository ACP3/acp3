<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Users\Controller\Widget\Index;

use ACP3\Core\Controller\AbstractWidgetAction;
use ACP3\Core\Controller\Context\WidgetContext;
use FOS\HttpCache\UserContext\DefaultHashGenerator;

class Hash extends AbstractWidgetAction
{
    /**
     * @var DefaultHashGenerator
     */
    private $hashGenerator;

    /**
     * Hash constructor.
     * @param WidgetContext $context
     * @param DefaultHashGenerator $hashGenerator
     */
    public function __construct(WidgetContext $context, DefaultHashGenerator $hashGenerator)
    {
        parent::__construct($context);

        $this->hashGenerator = $hashGenerator;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function execute()
    {
        $this->response->setVary('Cookie');
        $this->response->setPublic();
        $this->response->setMaxAge(60);
        $this->response->headers->add([
            'Content-type' => 'application/vnd.fos.user-context-hash',
            'X-User-Context-Hash' => $this->hashGenerator->generateHash()
        ]);

        return $this->response;
    }
}
