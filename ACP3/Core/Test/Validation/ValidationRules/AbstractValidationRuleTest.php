<?php

namespace ACP3\Core\Test\Validation\ValidationRules;

abstract class AbstractValidationRuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ACP3\Core\Validation\ValidationRules\ValidationRuleInterface
     */
    protected $validationRule;
    /**
     * @var \ACP3\Core\Validation\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validator;

    protected function setUp()
    {
        $this->validationRule->setMessage('Invalid value.');

        $this->validator = $this
            ->getMockBuilder(\ACP3\Core\Validation\Validator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->validator->registerValidationRule($this->validationRule);
    }

    /**
     * @return array
     */
    abstract public function validationRuleProvider();

    /**
     * @dataProvider validationRuleProvider
     *
     * @param mixed        $data
     * @param array|string $field
     * @param array        $extra
     * @param bool         $expected
     */
    public function testValidationRule($data, $field, $extra, $expected)
    {
        $this->assertEquals($expected, $this->validationRule->isValid($data, $field, $extra));
    }

    /**
     * @dataProvider validationRuleProvider
     *
     * @param mixed        $data
     * @param array|string $field
     * @param array        $extra
     * @param bool         $expected
     */
    public function testValidate($data, $field, $extra, $expected)
    {
        if ($expected === true) {
            $this->validator->expects($this->never())
                ->method('addError');
        } else {
            $this->validator->expects($this->once())
                ->method('addError')
                ->with('Invalid value.', $field);
        }

        $this->validationRule->validate(
            $this->validator,
            $data,
            $field,
            $extra
        );
    }
}
