<?php
/**
 * Copyright (c) by the ACP3 Developers.
 * See the LICENCE file at the top-level module directory for licencing details.
 */

namespace ACP3\Modules\ACP3\Wysiwygckeditor\WYSIWYG\Editor;

use ACP3\Core;
use ACP3\Core\WYSIWYG\Editor\Textarea;

/**
 * Implementation of the AbstractWYSIWYG class for CKEditor
 * @package ACP3\Modules\ACP3\Wysiwygckeditor\WYSIWYG\Editor
 */
class CKEditor extends Textarea
{
    /**
     * @var \ACP3\Core\Modules
     */
    protected $modules;
    /**
     * @var Core\I18n\LocaleInterface
     */
    private $locale;
    /**
     * @var \ACP3\Core\Environment\ApplicationPath
     */
    protected $appPath;
    /**
     * @var \ACP3\Modules\ACP3\Emoticons\Model\Repository\EmoticonsRepository
     */
    protected $emoticonRepository;
    /**
     * @var \ACP3\Modules\ACP3\Filemanager\Helpers
     */
    protected $filemanagerHelpers;

    /**
     * @var bool
     */
    private $isInitialized = false;

    /**
     * CKEditor constructor.
     *
     * @param \ACP3\Core\Modules $modules
     * @param Core\I18n\LocaleInterface $locale
     * @param \ACP3\Core\Environment\ApplicationPath $appPath
     */
    public function __construct(
        Core\Modules $modules,
        Core\I18n\LocaleInterface $locale,
        Core\Environment\ApplicationPath $appPath
    ) {
        $this->modules = $modules;
        $this->appPath = $appPath;
        $this->locale = $locale;
    }

    /**
     * @inheritdoc
     */
    public function getFriendlyName()
    {
        return 'CKEditor';
    }

    /**
     * @param \ACP3\Modules\ACP3\Emoticons\Model\Repository\EmoticonsRepository $emoticonRepository
     *
     * @return $this
     */
    public function setEmoticonRepository(\ACP3\Modules\ACP3\Emoticons\Model\Repository\EmoticonsRepository $emoticonRepository)
    {
        $this->emoticonRepository = $emoticonRepository;

        return $this;
    }

    /**
     * @param \ACP3\Modules\ACP3\Filemanager\Helpers $filemanagerHelpers
     *
     * @return $this
     */
    public function setFilemanagerHelpers(\ACP3\Modules\ACP3\Filemanager\Helpers $filemanagerHelpers)
    {
        $this->filemanagerHelpers = $filemanagerHelpers;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setParameters(array $params = [])
    {
        parent::setParameters($params);

        $this->config['toolbar'] = (isset($params['toolbar']) && $params['toolbar'] === 'simple') ? 'Basic' : 'Full';
        $this->config['height'] = ($params['height'] ?? 250) . 'px';
    }

    /**
     * @inheritdoc
     */
    public function getData()
    {
        $wysiwyg = [
            'friendly_name' => $this->getFriendlyName(),
            'id' => $this->id,
            'name' => $this->name,
            'value' => $this->value,
            'js' => $this->editor(),
            'advanced' => $this->advanced,
        ];

        if ($wysiwyg['advanced'] === true) {
            $wysiwyg['advanced_replace_content'] = 'CKEDITOR.instances.' . $wysiwyg['id'] . '.insertHtml(text);';
        }

        return ['wysiwyg' => $wysiwyg];
    }

    /**
     * Configures the CKEditor instance
     *
     * @return string
     */
    private function configure()
    {
        $this->config['entities'] = false;
        $this->config['extraPlugins'] = 'divarea,oembed,codemirror';
        $this->config['allowedContent'] = true;
        $this->config['language'] = $this->locale->getShortIsoCode();
        $this->config['codemirror'] = [
            'theme' => 'default',
            'lineNumbers' => true,
            'lineWrapping' => true,
            'matchBrackets' => true,
            'autoCloseTags' => true,
            'autoCloseBrackets' => true,
            'enableSearchTools' => true,
            'enableCodeFolding' => true,
            'enableCodeFormatting' => true,
            'autoFormatOnStart' => true,
            'autoFormatOnUncomment' => true,
            'highlightActiveLine' => true,
            'highlightMatches' => true,
            'showFormatButton' => false,
            'showCommentButton' => false,
            'showUncommentButton' => false
        ];

        // Full toolbar
        if ((!isset($this->config['toolbar']) || $this->config['toolbar'] !== 'Basic')) {
            $this->configureFullToolbar();
        } else { // basic toolbar
            $this->configureBasicToolbar();
        }

        return json_encode($this->config);
    }

    /**
     * @return array
     */
    private function editor()
    {
        $out = $this->init();

        // Add custom plugins
        $ckeditorPluginsDir = $this->appPath->getWebRoot() . 'ACP3/Modules/ACP3/Wysiwygckeditor/Resources/Assets/js/ckeditor/plugins/';

        $js = "CKEDITOR.plugins.addExternal('codemirror', '" . $ckeditorPluginsDir . "codemirror/');\n";
        $js .= "CKEDITOR.plugins.addExternal('divarea', '" . $ckeditorPluginsDir . "divarea/');\n";
        $js .= "CKEDITOR.plugins.addExternal('lineutils', '" . $ckeditorPluginsDir . "lineutils/');\n";
        $js .= "CKEDITOR.plugins.addExternal('oembed', '" . $ckeditorPluginsDir . "oembed/');\n";
        $js .= "CKEDITOR.plugins.addExternal('widgetselection', '" . $ckeditorPluginsDir . "widgetselection/');\n";
        $js .= "CKEDITOR.plugins.addExternal('widget', '" . $ckeditorPluginsDir . "widget/');\n";

        $config = $this->configure();
        if (!empty($config)) {
            $js .= "CKEDITOR.replace('" . $this->id . "', " . $config . ");";
        } else {
            $js .= "CKEDITOR.replace('" . $this->id . "');";
        }

        $out .= $this->script($js);

        return [
            'template' => 'Wysiwygckeditor/ckeditor.tpl',
            'config' => $out
        ];
    }

    /**
     * Prints javascript code.
     *
     * @param string $js
     *
     * @return string
     */
    private function script($js)
    {
        $out = "<script type=\"text/javascript\">";
        $out .= $js;
        $out .= "</script>\n";

        return $out;
    }

    /**
     * @return string
     */
    private function init()
    {
        if ($this->isInitialized === true) {
            return "";
        }

        $this->isInitialized = true;
        $basePath = $this->appPath->getWebRoot() . 'vendor/ckeditor/ckeditor/';
        $out = "";

        // Skip relative paths...
        if (strpos($basePath, '..') !== 0) {
            $out .= $this->script("window.CKEDITOR_BASEPATH='" . $basePath . "';");
        }

        $out .= "<script type=\"text/javascript\" src=\"" . $basePath . "ckeditor.js\"></script>\n";

        return $out;
    }

    private function applyEmoticons()
    {
        $this->config['smiley_path'] = $this->appPath->getWebRoot() . 'uploads/emoticons/';
        $this->config['smiley_images'] = $this->config['smiley_descriptions'] = '';
        $emoticons = $this->emoticonRepository->getAll();
        $cEmoticons = count($emoticons);

        $images = $descriptions = [];
        for ($i = 0; $i < $cEmoticons; ++$i) {
            $images[] = $emoticons[$i]['img'];
            $descriptions[] = $emoticons[$i]['description'];
        }

        $this->config['smiley_images'] = $images;
        $this->config['smiley_descriptions'] = $descriptions;
    }

    private function applyFileManagerPaths()
    {
        $kcfinderPath = $this->filemanagerHelpers->getFilemanagerPath();
        $fileBrowserUri = $kcfinderPath . 'browse.php?opener=ckeditor%s&cms=acp3';
        $uploadUri = $kcfinderPath . 'upload.php?opener=ckeditor%s&cms=acp3';

        $this->config['filebrowserBrowseUrl'] = sprintf($fileBrowserUri, '&type=files');
        $this->config['filebrowserImageBrowseUrl'] = sprintf($fileBrowserUri, '&type=gallery');
        $this->config['filebrowserFlashBrowseUrl'] = sprintf($fileBrowserUri, '&type=files');
        $this->config['filebrowserUploadUrl'] = sprintf($uploadUri, '&type=files');
        $this->config['filebrowserImageUploadUrl'] = sprintf($uploadUri, '&type=gallery');
        $this->config['filebrowserFlashUploadUrl'] = sprintf($uploadUri, '&type=files');
    }

    private function configureFullToolbar()
    {
        $this->config['extraPlugins'] = 'codemirror,divarea,lineutils,oembed,widget';

        if ($this->filemanagerHelpers instanceof \ACP3\Modules\ACP3\Filemanager\Helpers) {
            $this->applyFileManagerPaths();
        }

        // Toolbar configuration
        $this->config['toolbarGroups'] = [
            ['name' => 'document', 'groups' => ['mode', 'document', 'doctools']],
            ['name' => 'clipboard', 'groups' => ['clipboard', 'undo']],
            ['name' => 'editing', 'groups' => ['find', 'selection', 'spellchecker']],
            ['name' => 'forms'],
            '/',
            ['name' => 'basicstyles', 'groups' => ['basicstyles', 'cleanup']],
            ['name' => 'paragraph', 'groups' => ['list', 'indent', 'blocks', 'align', 'bidi']],
            ['name' => 'links'],
            ['name' => 'insert'],
            '/',
            ['name' => 'styles'],
            ['name' => 'colors'],
            ['name' => 'tools'],
            ['name' => 'others'],
            ['name' => 'about']
        ];
        ;

        // Include emoticons, if available
        if ($this->modules->isActive('emoticons') === true) {
            $this->applyEmoticons();
        }
    }

    private function configureBasicToolbar()
    {
        $this->config['extraPlugins'] = 'divarea,codemirror';
        $this->config['toolbar'] = [
            [
                'Source',
                '-',
                'Undo',
                'Redo',
                '-',
                'Bold',
                'Italic',
                '-',
                'NumberedList',
                'BulletedList',
                '-',
                'Link',
                'Unlink',
                '-',
                'About'
            ]
        ];
    }
}
