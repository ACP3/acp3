<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Installer\Controller\Installer\Index;

use ACP3\Core\Controller\Context\Context;
use ACP3\Core\Date;
use ACP3\Core\Environment\ThemePathInterface;
use ACP3\Core\Helpers\Date as DateHelper;
use ACP3\Core\Helpers\Forms;
use ACP3\Modules\ACP3\Installer\Helpers\Navigation;

class Install extends AbstractAction
{
    public function __construct(
        Context $context,
        private readonly ThemePathInterface $theme,
        Navigation $navigation,
        private readonly Date $date,
        private readonly DateHelper $dateHelper,
        private readonly Forms $forms,
    ) {
        parent::__construct($context, $navigation);
    }

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'time_zones' => $this->dateHelper->getTimeZones(date_default_timezone_get()),
            'form' => array_merge($this->getFormDefaults(), $this->request->getPost()->all()),
            'designs' => $this->getThemeFormOptions(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function getFormDefaults(): array
    {
        return [
            'db_host' => 'localhost',
            'db_pre' => '',
            'db_user' => '',
            'db_name' => '',
            'user_name' => '',
            'mail' => '',
            'date_format_long' => $this->date->getDateFormatLong(),
            'date_format_short' => $this->date->getDateFormatShort(),
            'title' => 'ACP3',
        ];
    }

    /**
     * @return array<string, mixed>[]
     */
    protected function getThemeFormOptions(): array
    {
        $themes = $this->theme->getAvailableThemes();

        $options = [];
        foreach ($themes as $themeName => $themeInfo) {
            if ($themeName === 'acp3-installer') {
                continue;
            }

            $options[$themeName] = $themeInfo['name'];
        }

        return $this->forms->choicesGenerator('design', $options);
    }
}
