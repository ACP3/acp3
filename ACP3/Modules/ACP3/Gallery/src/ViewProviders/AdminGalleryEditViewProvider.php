<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Gallery\ViewProviders;

use ACP3\Core\Breadcrumb\Title;
use ACP3\Core\Helpers\Forms;
use ACP3\Core\Helpers\FormToken;
use ACP3\Core\Http\RequestInterface;
use ACP3\Modules\ACP3\Gallery\Helpers as GalleryHelpers;

class AdminGalleryEditViewProvider
{
    public function __construct(private readonly Forms $formsHelper, private readonly FormToken $formTokenHelper, private readonly RequestInterface $request, private readonly Title $title)
    {
    }

    /**
     * @param array<string, mixed> $gallery
     *
     * @return array<string, mixed>
     */
    public function __invoke(array $gallery): array
    {
        $this->title->setPageTitlePrefix($gallery['title']);

        return [
            'active' => $this->formsHelper->yesNoCheckboxGenerator('active', $gallery['active']),
            'form' => array_merge($gallery, $this->request->getPost()->all()),
            'form_token' => $this->formTokenHelper->renderFormToken(),
            'SEO_URI_PATTERN' => GalleryHelpers::URL_KEY_PATTERN_GALLERY,
            'SEO_ROUTE_NAME' => !empty($gallery['id']) ? \sprintf(GalleryHelpers::URL_KEY_PATTERN_GALLERY, $gallery['id']) : '',
        ];
    }
}
