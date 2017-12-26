<?php
namespace ACP3\Modules\ACP3\News\Validation;

use ACP3\Core;
use ACP3\Core\Validation\ValidationRules\ExternalLinkValidationRule;
use ACP3\Modules\ACP3\Categories;

/**
 * Class AdminFormValidation
 * @package ACP3\Modules\ACP3\News\Validation
 */
class AdminFormValidation extends Core\Validation\AbstractFormValidation
{
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
                Core\Validation\ValidationRules\InArrayValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'active',
                    'message' => $this->translator->t('news', 'select_active'),
                    'extra' => [
                        'haystack' => [0, 1]
                    ]
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\DateValidationRule::class,
                [
                    'data' => $formData,
                    'field' => ['start', 'end'],
                    'message' => $this->translator->t('system', 'select_date')
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'title',
                    'message' => $this->translator->t('news', 'title_to_short')
                ]
            )
            ->addConstraint(
                Core\Validation\ValidationRules\NotEmptyValidationRule::class,
                [
                    'data' => $formData,
                    'field' => 'text',
                    'message' => $this->translator->t('news', 'text_to_short')
                ]
            )
            ->addConstraint(
                Categories\Validation\ValidationRules\CategoryExistsValidationRule::class,
                [
                    'data' => $formData,
                    'field' => ['cat', 'cat_create'],
                    'message' => $this->translator->t('news', 'select_category')
                ]
            )
            ->addConstraint(
                ExternalLinkValidationRule::class,
                [
                    'data' => $formData,
                    'field' => ['link_title', 'uri', 'target'],
                    'message' => $this->translator->t('news', 'complete_hyperlink_statements')
                ]
            );

        $this->validator->dispatchValidationEvent(
            'seo.validation.validate_uri_alias',
            $formData,
            ['path' => $this->uriAlias]
        );

        $this->validator->validate();
    }
}
