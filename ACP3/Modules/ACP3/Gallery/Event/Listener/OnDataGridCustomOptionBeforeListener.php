<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Gallery\Event\Listener;

use ACP3\Core\ACL;
use ACP3\Core\Helpers\DataGrid\ColumnRenderer\Event\CustomOptionEvent;
use ACP3\Core\I18n\Translator;

/**
 * Class OnDataGridCustomOptionBeforeListener
 * @package ACP3\Modules\ACP3\Newsletter\Event\Listener
 */
class OnDataGridCustomOptionBeforeListener
{
    /**
     * @var \ACP3\Core\ACL
     */
    protected $acl;
    /**
     * @var \ACP3\Core\I18n\Translator
     */
    protected $translator;

    /**
     * OnDataGridCustomOptionBeforeListener constructor.
     *
     * @param \ACP3\Core\ACL             $acl
     * @param \ACP3\Core\I18n\Translator $translator
     */
    public function __construct(
        ACL $acl,
        Translator $translator
    ) {
        $this->acl = $acl;
        $this->translator = $translator;
    }

    /**
     * @param \ACP3\Core\Helpers\DataGrid\ColumnRenderer\Event\CustomOptionEvent $customOptionEvent
     */
    public function addPicturesIndexButton(CustomOptionEvent $customOptionEvent)
    {
        if ($customOptionEvent->getIdentifier() === '#gallery-data-grid' &&
            $this->acl->hasPermission('admin/gallery/pictures/index') === true
        ) {
            $dbResultRow = $customOptionEvent->getDbResultRow();

            $customOptionEvent->getOptionRenderer()->addOption(
                'acp/gallery/pictures/index/id_' . $dbResultRow['id'],
                $this->translator->t('gallery', 'admin_pictures_index'),
                'glyphicon-picture',
                'btn-default'
            );
        }
    }
}