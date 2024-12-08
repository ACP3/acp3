<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\Menus\Installer;

use ACP3\Core\I18n\Translator;
use ACP3\Core\Installer\SchemaHelper;

class SampleData extends \ACP3\Core\Installer\AbstractSampleData
{
    public function __construct(
        SchemaHelper $schemaHelper,
        private readonly Translator $translator,
    ) {
        parent::__construct($schemaHelper);
    }

    public function sampleData(): array
    {
        $translator = $this->translator;

        return [
            "INSERT INTO `{pre}menus` VALUES (1, 'main', '{$translator->t('menus', 'sample_data_menu_main')}');",
            "INSERT INTO `{pre}menus` VALUES (2, 'sidebar', '{$translator->t('menus', 'sample_data_menu_sidebar')}');",
            "INSERT INTO `{pre}menu_items` VALUES (1, 1, 1, 1, 0, 1, 4, 1, '{$translator->t('menus', 'sample_data_menu_item_news')}', 'news', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (2, 1, 1, 1, 1, 2, 3, 1, '{$translator->t('menus', 'sample_data_menu_item_newsletter')}', 'newsletter', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (3, 1, 1, 3, 0, 5, 6, 1, '{$translator->t('menus', 'sample_data_menu_item_files')}', 'files', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (4, 1, 1, 4, 0, 7, 8, 1, '{$translator->t('menus', 'sample_data_menu_item_gallery')}', 'gallery', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (5, 1, 1, 5, 0, 9, 10, 1, '{$translator->t('menus', 'sample_data_menu_item_guestbook')}', 'guestbook', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (6, 1, 1, 6, 0, 11, 12, 1, '{$translator->t('menus', 'sample_data_menu_item_polls')}', 'polls', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (7, 1, 1, 7, 0, 13, 14, 1, '{$translator->t('menus', 'sample_data_menu_item_search')}', 'search', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (8, 1, 2, 8, 0, 15, 16, 1, '{$translator->t('menus', 'sample_data_menu_item_contact')}', 'contact', 1);",
            "INSERT INTO `{pre}menu_items` VALUES (9, 2, 2, 9, 0, 17, 18, 1, '{$translator->t('menus', 'sample_data_menu_item_imprint')}', 'contact/index/imprint/', 1);",
        ];
    }
}
