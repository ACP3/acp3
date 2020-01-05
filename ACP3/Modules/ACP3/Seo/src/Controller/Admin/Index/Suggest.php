<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Seo\Controller\Admin\Index;

use ACP3\Core\Controller\AbstractFrontendAction;
use ACP3\Core\Controller\Context\FrontendContext;
use ACP3\Core\Helpers\StringFormatter;
use Symfony\Component\HttpFoundation\JsonResponse;

class Suggest extends AbstractFrontendAction
{
    /**
     * @var StringFormatter
     */
    protected $stringFormatter;

    /**
     * Suggest constructor.
     */
    public function __construct(FrontendContext $context, StringFormatter $stringFormatter)
    {
        parent::__construct($context);

        $this->stringFormatter = $stringFormatter;
    }

    /**
     * @return JsonResponse
     */
    public function execute()
    {
        $response = [];
        if ($this->request->getPost()->count() > 0) {
            $formData = $this->request->getPost()->all();

            if (!empty($formData['title'])) {
                $alias = $this->stringFormatter->makeStringUrlSafe($formData['title']);

                if (!empty($formData['prefix'])) {
                    $alias = $this->stringFormatter->makeStringUrlSafe($formData['prefix']) . '/' . $alias;
                }

                $response = ['alias' => $alias];
            }
        }

        return new JsonResponse($response);
    }
}