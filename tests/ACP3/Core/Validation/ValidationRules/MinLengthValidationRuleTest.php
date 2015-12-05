<?php

class MinLengthValidationRuleTest extends AbstractValidationRuleTest
{
    protected function setUp()
    {
        $this->validationRule = new \ACP3\Core\Validation\ValidationRules\MinLengthValidationRule();
    }

    /**
     * @return array
     */
    public function validationRuleProvider()
    {
        return [
            'valid-data-string' => ['foobar', '', ['length' => 3], true],
            'valid-data-array' => [['foo' => 'foobar'], 'foo', ['length' => 3], true],
            'invalid-data-string' => ['foobar', '', ['length' => 7], false],
            'invalid-data-array' => [['foo' => 'foobar'], 'foo', ['length' => 7], false],
            'invalid-no-data' => [null, null, [], false]
        ];
    }
}