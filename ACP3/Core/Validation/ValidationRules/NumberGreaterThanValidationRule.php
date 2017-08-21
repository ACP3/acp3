<?php
namespace ACP3\Core\Validation\ValidationRules;

/**
 * Class NumberGreaterThanValidationRule
 * @package ACP3\Core\Validation\ValidationRules
 */
class NumberGreaterThanValidationRule extends AbstractValidationRule
{
    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (is_array($data) && array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        $comparator = $extra['value'] ?? 0;

        return $data > $comparator;
    }
}
