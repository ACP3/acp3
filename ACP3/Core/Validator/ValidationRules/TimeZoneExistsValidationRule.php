<?php
namespace ACP3\Core\Validator\ValidationRules;

/**
 * Class TimeZoneExistsValidationRule
 * @package ACP3\Core\Validator\ValidationRules
 */
class TimeZoneExistsValidationRule extends AbstractValidationRule
{
    const NAME = 'time_zone_exists';

    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (is_array($data) && array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        $bool = true;
        try {
            new \DateTimeZone($data);
        } catch (\Exception $e) {
            $bool = false;
        }
        return $bool;
    }
}