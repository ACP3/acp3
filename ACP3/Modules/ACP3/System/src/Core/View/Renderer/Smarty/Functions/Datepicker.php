<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\System\Core\View\Renderer\Smarty\Functions;

use ACP3\Core\Date;
use ACP3\Core\Http\RequestInterface;
use ACP3\Core\Settings\SettingsInterface;
use ACP3\Core\Validation\ValidationRules\DateValidationRule;
use ACP3\Core\View\Renderer\Smarty\Functions\AbstractFunction;
use ACP3\Modules\ACP3\System\Installer\Schema;

class Datepicker extends AbstractFunction
{
    public function __construct(private readonly SettingsInterface $settings, private readonly RequestInterface $request, private readonly Date $date, private readonly DateValidationRule $dateValidationRule)
    {
    }

    /**
     * @throws \SmartyException
     * @throws \Exception
     */
    public function __invoke(array $params, \Smarty_Internal_Template $smarty): string
    {
        $params = $this->mergeParameters($params);

        $smarty->smarty->assign('label', $params['label']);
        $smarty->smarty->assign('datepicker', $this->getDatepickerConfig(
            $params['name'],
            $params['value'],
            $params['withTime'],
            $params['inputFieldOnly']
        ));

        return $smarty->smarty->fetch('asset:System/Partials/datepicker.tpl');
    }

    /**
     * @param array<string, mixed> $params
     *
     * @return array<string, mixed>
     */
    private function mergeParameters(array $params): array
    {
        $defaults = [
            'name' => '',
            'value' => '',
            'withTime' => true,
            'inputFieldOnly' => false,
            'label' => '',
        ];

        return array_merge($defaults, $params);
    }

    /**
     * Displays an input field with an associated datepicker.
     *
     * @param string[]|string $name
     * @param string[]|string $value
     *
     * @return array<string, mixed>
     *
     * @throws \Exception
     */
    public function getDatepickerConfig(
        array|string $name,
        array|string $value = '',
        bool $showTime = true,
        bool $inputFieldOnly = false,
    ): array {
        $datePicker = [
            'range' => \is_array($name),
            'with_time' => $showTime,
            'length' => $showTime === true ? 16 : 10,
            'input_only' => $inputFieldOnly,
            'config' => [
                'altFormat' => $this->getPickerDateFormat($showTime),
                'enableTime' => $showTime,
            ],
        ];

        if (\is_array($name) && \is_array($value)) {
            $datePicker['name_start'] = $name[0];
            $datePicker['name_end'] = $name[1];
            $datePicker['id_start'] = $this->getInputId($name[0]);
            $datePicker['id_end'] = $this->getInputId($name[1]);

            $datePicker = array_merge($datePicker, $this->fetchRangeDatePickerValues($name, $value, $showTime));

            $datePicker['config'] = array_merge(
                $datePicker['config'],
                [
                    'start' => '#' . $datePicker['id_start'],
                    'startDefaultDate' => $datePicker['value_start_r'],
                    'end' => '#' . $datePicker['id_end'],
                    'endDefaultDate' => $datePicker['value_end_r'],
                ]
            );
        } elseif (\is_string($name) && \is_string($value)) { // Einfaches Inputfeld mit Datepicker
            $datePicker['name'] = $name;
            $datePicker['id'] = $this->getInputId($name);
            $datePicker['value'] = $this->fetchSimpleDatePickerValue($name, $value, $showTime);
            $datePicker['config'] = array_merge(
                $datePicker['config'],
                [
                    'element' => '#' . $datePicker['id'],
                ]
            );
        }

        return $datePicker;
    }

    private function getInputId(string $fieldName): string
    {
        return 'date-' . str_replace('_', '-', $fieldName);
    }

    /**
     * @param string[] $name
     * @param string[] $value
     *
     * @return array<string, string>
     *
     * @throws \Exception
     */
    private function fetchRangeDatePickerValues(array $name, array $value, bool $showTime): array
    {
        if ($this->request->getPost()->has($name[0]) && $this->request->getPost()->has($name[1])) {
            $valueStart = $this->request->getPost()->get($name[0]);
            $valueEnd = $this->request->getPost()->get($name[1]);
            $valueStartR = $this->date->format($valueStart, 'c', false);
            $valueEndR = $this->date->format($valueEnd, 'c', false);
        } elseif ($this->dateValidationRule->isValid($value) === true) {
            $valueStart = $this->date->format($value[0], $this->getDateFormat($showTime));
            $valueEnd = $this->date->format($value[1], $this->getDateFormat($showTime));
            $valueStartR = $this->date->format($value[0], 'c');
            $valueEndR = $this->date->format($value[1], 'c');
        } else {
            $valueStart = $this->date->format('now', $this->getDateFormat($showTime), false);
            $valueEnd = $this->date->format('now', $this->getDateFormat($showTime), false);
            $valueStartR = $this->date->format('now', 'c', false);
            $valueEndR = $this->date->format('now', 'c', false);
        }

        return [
            'value_start' => $valueStart,
            'value_end' => $valueEnd,
            'value_start_r' => $valueStartR,
            'value_end_r' => $valueEndR,
        ];
    }

    /**
     * @throws \Exception
     */
    private function fetchSimpleDatePickerValue(string $name, string $value, bool $showTime): string
    {
        if ($this->request->getPost()->has($name)) {
            return $this->request->getPost()->get($name, '');
        }
        if ($this->dateValidationRule->isValid($value) === true) {
            return $this->date->format($value, $this->getDateFormat($showTime));
        }

        return $this->date->format('now', $this->getDateFormat($showTime), false);
    }

    private function getPickerDateFormat(bool $showTime): string
    {
        return $this->settings->getSettings(Schema::MODULE_NAME)[$showTime ? 'date_format_long' : 'date_format_short'];
    }

    private function getDateFormat(bool $showTime): string
    {
        return $showTime === true ? Date::DEFAULT_DATE_FORMAT_LONG : Date::DEFAULT_DATE_FORMAT_SHORT;
    }
}
