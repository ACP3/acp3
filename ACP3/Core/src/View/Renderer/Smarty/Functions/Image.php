<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\View\Renderer\Smarty\Functions;

use ACP3\Core\Assets\FileResolver;
use ACP3\Core\Http\RequestInterface;

class Image extends AbstractFunction
{
    public function __construct(private readonly RequestInterface $request, private readonly FileResolver $fileResolver)
    {
    }

    public function __invoke(array $params, \Smarty_Internal_Template $smarty): string
    {
        if (isset($params['file'], $params['module']) === true && (bool) preg_match('=\./=', (string) $params['file']) === false) {
            $path = $this->fileResolver->getWebStaticAssetPath($params['module'], 'Assets/img', $params['file']);

            if (isset($params['absolute']) && $params['absolute'] === true) {
                $path = $this->request->getScheme() . '://' . $this->request->getHttpHost() . $path;
            }

            return $path;
        }

        throw new \InvalidArgumentException(\sprintf('Not all necessary arguments for the function %s were passed!', __FUNCTION__));
    }
}
