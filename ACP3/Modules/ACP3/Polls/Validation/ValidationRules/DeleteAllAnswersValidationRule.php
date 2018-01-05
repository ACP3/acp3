<?php
namespace ACP3\Modules\ACP3\Polls\Validation\ValidationRules;

class DeleteAllAnswersValidationRule extends AbstractAnswerValidationRule
{
    /**
     * @inheritdoc
     */
    public function isValid($data, $field = '', array $extra = [])
    {
        if (is_array($data) && array_key_exists($field, $data)) {
            return $this->isValid($data[$field], $field, $extra);
        }

        if (is_array($data)) {
            list($markedAnswers, ) = $this->validateAnswers($data);

            return count($data) - $markedAnswers >= 2;
        }

        return false;
    }
}
