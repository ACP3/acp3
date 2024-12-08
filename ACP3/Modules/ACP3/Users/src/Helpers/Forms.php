<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Users\Helpers;

use ACP3\Core\Http\RequestInterface;
use ACP3\Core\I18n\CountryList;
use ACP3\Core\I18n\Translator;

class Forms
{
    public function __construct(protected Translator $translator, private readonly CountryList $country, protected RequestInterface $request, protected \ACP3\Core\Helpers\Forms $formsHelpers)
    {
    }

    /**
     * @return array<string, mixed>[]
     */
    public function fetchContactDetails(
        string $defaultMail = '',
        string $defaultWebsite = '',
        string $defaultIcqNumber = '',
        string $defaultSkypeName = '',
    ): array {
        return [
            [
                'name' => 'mail',
                'lang' => $this->translator->t('system', 'email_address'),
                'value' => $this->request->getPost()->get('mail', $defaultMail),
                'maxlength' => '120',
            ],
            [
                'name' => 'website',
                'lang' => $this->translator->t('system', 'website'),
                'value' => $this->request->getPost()->get('website', $defaultWebsite),
                'maxlength' => '120',
            ],
            [
                'name' => 'icq',
                'lang' => $this->translator->t('users', 'icq'),
                'value' => $this->request->getPost()->get('icq', $defaultIcqNumber),
                'maxlength' => '9',
            ],
            [
                'name' => 'skype',
                'lang' => $this->translator->t('users', 'skype'),
                'value' => $this->request->getPost()->get('skype', $defaultSkypeName),
                'maxlength' => '28',
            ],
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    public function generateWorldCountriesSelect(string $defaultValue = ''): array
    {
        return $this->formsHelpers->choicesGenerator(
            'country',
            $this->country->worldCountries(),
            $defaultValue
        );
    }

    /**
     * @return array<string, array<string, mixed>[]>
     */
    public function fetchUserSettingsFormFields(
        int $displayAddress = 0,
        int $displayBirthday = 0,
        int $displayCountry = 0,
        int $displayMail = 0,
    ): array {
        return [
            'address_display' => $this->displayAddress($displayAddress),
            'birthday_display' => $this->displayBirthday($displayBirthday),
            'country_display' => $this->displayCountry($displayCountry),
            'mail_display' => $this->displayMail($displayMail),
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function displayAddress(int $value): array
    {
        return $this->formsHelpers->yesNoCheckboxGenerator('address_display', $value);
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function displayBirthday(int $value): array
    {
        $displayBirthday = [
            0 => $this->translator->t('users', 'birthday_hide'),
            1 => $this->translator->t('users', 'birthday_display_completely'),
            2 => $this->translator->t('users', 'birthday_hide_year'),
        ];

        return $this->formsHelpers->checkboxGenerator('birthday_display', $displayBirthday, $value);
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function displayCountry(int $value): array
    {
        return $this->formsHelpers->yesNoCheckboxGenerator('country_display', $value);
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function displayMail(int $value): array
    {
        return $this->formsHelpers->yesNoCheckboxGenerator('mail_display', $value);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserProfileFormFields(string $birthday = '', string $country = '', int $gender = 1): array
    {
        return [
            'birthday' => $birthday,
            'countries' => $this->generateWorldCountriesSelect($country),
            'gender' => $this->fetchGenderField($gender),
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function fetchGenderField(int $currentGender): array
    {
        $genders = [
            1 => $this->translator->t('users', 'gender_not_specified'),
            2 => $this->translator->t('users', 'gender_female'),
            3 => $this->translator->t('users', 'gender_male'),
        ];

        return $this->formsHelpers->choicesGenerator('gender', $genders, $currentGender);
    }
}
