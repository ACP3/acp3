<?php

/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENSE file at the top-level module directory for licensing details.
 */

namespace ACP3\Modules\ACP3\News\Installer;

use ACP3\Core\Date;
use ACP3\Core\I18n\Translator;
use ACP3\Core\Modules\Installer\AbstractSampleData;
use ACP3\Core\Modules\SchemaHelper;

class SampleData extends AbstractSampleData
{
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    private $translator;

    public function __construct(
        SchemaHelper $schemaHelper,
        Translator $translator
    ) {
        parent::__construct($schemaHelper);

        $this->translator = $translator;
    }

    /**
     * @return array
     */
    public function sampleData()
    {
        $currentDate = \gmdate(Date::DEFAULT_DATE_FORMAT_FULL);
        $translator = $this->translator;

        return [
            "INSERT INTO `{pre}categories` VALUES (1, 1, 0, 1, 2, '{$translator->t('news', 'sample_data_category_name')}', '', '{$translator->t('news', 'sample_data_category_description')}', '{$this->schemaHelper->getModuleId(Schema::MODULE_NAME)}');",
            "INSERT INTO `{pre}news` (id, active, start, end, updated_at, title, text, readmore, category_id, uri, target, link_title, user_id) VALUES (1, 1, '{$currentDate}', '{$currentDate}', '{$currentDate}', '{$translator->t('news', 'sample_data_news_headline')}', '{$translator->t('news', 'sample_data_news_text')}', 1, 1, '', 1, '', 1);",
        ];
    }
}
