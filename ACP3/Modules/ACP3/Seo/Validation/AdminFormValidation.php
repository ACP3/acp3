<?php
namespace ACP3\Modules\ACP3\Seo\Validation;

use ACP3\Core;

/**
 * Class AdminFormValidation
 * @package ACP3\Modules\ACP3\Seo\Validation
 */
class AdminFormValidation extends Core\Validation\AbstractFormValidation
{
    /**
     * @var string
     */
    protected $uriAlias = '';

    /**
     * @param string $uriAlias
     *
     * @return $this
     */
    public function setUriAlias($uriAlias)
    {
        $this->uriAlias = $uriAlias;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate(array $formData)
    {
        $this->validator
            ->addConstraint(Core\Validation\ValidationRules\FormTokenValidationRule::class)
            ->addConstraint(
                Core\Validation\ValidationRules\InternalUriValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'uri',
                    'message' => $this->translator->t('seo', 'type_in_valid_resource')
                ])
            ->addConstraint(
                Core\Validation\ValidationRules\InArrayValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'seo_robots',
                    'message' => $this->translator->t('seo', 'select_robots'),
                    'extra' => [
                        'haystack' => [0, 1, 2, 3, 4]
                    ]
                ]);

        $this->validator->dispatchValidationEvent(
            'seo.validation.validate_uri_alias',
            $formData,
            ['path' => $this->uriAlias]
        );

        $this->validator->validate();
    }
}
