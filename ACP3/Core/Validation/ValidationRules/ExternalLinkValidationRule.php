<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Core\Validation\ValidationRules;

class ExternalLinkValidationRule extends AbstractValidationRule
{
    /**
     * @var \ACP3\Core\Validation\ValidationRules\InArrayValidationRule
     */
    protected $inArrayValidationRule;

    /**
     * ExternalLinkValidationRule constructor.
     *
     * @param \ACP3\Core\Validation\ValidationRules\InArrayValidationRule $inArrayValidationRule
     */
    public function __construct(InArrayValidationRule $inArrayValidationRule)
    {
        $this->inArrayValidationRule = $inArrayValidationRule;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (\is_array($data) && \is_array($field)) {
            $linkTitle = \reset($field);
            $uri = \next($field);
            $target = \next($field);

            return $this->isValidLink($data[$linkTitle], $data[$uri], $data[$target]);
        }

        return false;
    }

    /**
     * @param string $linkTitle
     * @param string $uri
     * @param int    $target
     *
     * @return bool
     */
    protected function isValidLink($linkTitle, $uri, $target)
    {
        if (empty($linkTitle)) {
            return true;
        }

        return !empty($uri) && $this->inArrayValidationRule->isValid($target, '', ['haystack' => [1, 2]]);
    }
}
