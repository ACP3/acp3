<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\WYSIWYG;

use ACP3\Core\WYSIWYG\Editor\AbstractWYSIWYG;

class WysiwygEditorRegistrar
{
    /**
     * @var AbstractWYSIWYG[]
     */
    protected $wysiwygEditors = [];

    /**
     * @param string $serviceId
     *
     * @return $this
     */
    public function registerWysiwygEditor($serviceId, AbstractWYSIWYG $wysiwygEditor)
    {
        $this->wysiwygEditors[$serviceId] = $wysiwygEditor;

        return $this;
    }

    /**
     * @return \ACP3\Core\WYSIWYG\Editor\AbstractWYSIWYG[]
     */
    public function all()
    {
        return $this->wysiwygEditors;
    }

    /**
     * @param string $serviceId
     *
     * @return bool
     */
    public function has($serviceId)
    {
        return isset($this->wysiwygEditors[$serviceId]);
    }

    /**
     * @param string $serviceId
     *
     * @return AbstractWYSIWYG
     */
    public function get($serviceId)
    {
        if ($this->has($serviceId)) {
            return $this->wysiwygEditors[$serviceId];
        }

        throw new \InvalidArgumentException(\sprintf('Can not find the WYSIWYG-Editor with the name: %s', $serviceId));
    }
}