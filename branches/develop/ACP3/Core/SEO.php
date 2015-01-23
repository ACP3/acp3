<?php
namespace ACP3\Core;

use ACP3\Core\Helpers\Forms;
use ACP3\Core\Router\Aliases;

/**
 * Class SEO
 * @package ACP3\Core
 */
class SEO
{
    /**
     * @var \ACP3\Core\Router\Aliases
     */
    protected $aliases;
    /**
     * @var \ACP3\Core\Lang
     */
    protected $lang;
    /**
     * @var \ACP3\Core\Request
     */
    protected $request;
    /**
     * @var \ACP3\Core\Helpers\Forms
     */
    protected $formsHelper;
    /**
     * @var \ACP3\Core\Config
     */
    protected $seoConfig;
    /**
     * @var \ACP3\Modules\Seo\Cache
     */
    protected $seoCache;
    /**
     * @var \ACP3\Modules\Seo\Model
     */
    protected $seoModel;

    /**
     * Gibt die nächste Seite an
     *
     * @var string
     */
    protected $nextPage = '';
    /**
     * Gibt die vorherige Seite an
     *
     * @var string
     */
    protected $previousPage = '';
    /**
     * Kanonische URL
     *
     * @var string
     */
    protected $canonical = '';
    /**
     * @var null|array
     */
    protected $aliasesCache = null;
    /**
     * @var string
     */
    protected $metaDescriptionPostfix = '';

    /**
     * @param \ACP3\Core\Lang           $lang
     * @param \ACP3\Core\Request        $request
     * @param \ACP3\Core\Router\Aliases $aliases
     * @param \ACP3\Core\Helpers\Forms  $formsHelper
     * @param \ACP3\Modules\Seo\Cache   $seoCache
     * @param \ACP3\Core\Config         $seoConfig
     * @param \ACP3\Modules\Seo\Model   $seoModel
     */
    public function __construct(
        Lang $lang,
        Request $request,
        Aliases $aliases,
        Forms $formsHelper,
        \ACP3\Modules\Seo\Cache $seoCache,
        Config $seoConfig,
        \ACP3\Modules\Seo\Model $seoModel)
    {
        $this->lang = $lang;
        $this->request = $request;
        $this->aliases = $aliases;
        $this->formsHelper = $formsHelper;
        $this->seoCache = $seoCache;
        $this->seoConfig = $seoConfig;
        $this->seoModel = $seoModel;
    }

    /**
     * Gibt die für die jeweilige Seite gesetzten Metatags zurück
     *
     * @return string
     */
    public function getMetaTags()
    {
        return [
            'description' => $this->request->area === 'admin' ? '' : $this->getPageDescription(),
            'keywords' => $this->request->area === 'admin' ? '' : $this->getPageKeywords(),
            'robots' => $this->request->area === 'admin' ? 'noindex,nofollow' : $this->getPageRobotsSetting(),
            'previous_page' => $this->previousPage,
            'next_page' => $this->nextPage,
            'canonical' => $this->canonical,
        ];
    }

    /**
     * Gibt die Beschreibung der aktuell angezeigten Seite zurück
     *
     * @return string
     */
    public function getPageDescription()
    {
        $description = $this->getDescription($this->request->getUriWithoutPages());
        if (empty($description)) {
            $description = $this->getDescription($this->request->mod . '/' . $this->request->controller . '/' . $this->request->file);
        }
        if (empty($description)) {
            $description = $this->getDescription($this->request->mod);
        }
        if (empty($description)) {
            $description = $this->seoConfig->getSettings()['meta_description'];
        }

        $postfix = '';
        if (!empty($description) && !empty($this->metaDescriptionPostfix)) {
            $postfix .= ' - ' . $this->metaDescriptionPostfix;
        }

        return $description . $postfix;
    }

    /**
     * Gibt die Beschreibung der Seite zurück
     *
     * @param string $path
     *
     * @return string
     */
    public function getDescription($path)
    {
        return $this->getSeoInformation($path, 'description');
    }

    /**
     * Gibt die Keywords der aktuell angezeigten Seite oder der
     * Elternseite zurück
     *
     * @return string
     */
    public function getPageKeywords()
    {
        $keywords = $this->getKeywords($this->request->getUriWithoutPages());
        if (empty($keywords)) {
            $keywords = $this->getKeywords($this->request->mod . '/' . $this->request->controller . '/' . $this->request->file);
        }
        if (empty($keywords)) {
            $keywords = $this->getKeywords($this->request->mod);
        }

        return strtolower(!empty($keywords) ? $keywords : $this->seoConfig->getSettings()['meta_keywords']);
    }

    /**
     * Gibt die Schlüsselwörter der Seite zurück
     *
     * @param string $path
     *
     * @return string
     */
    public function getKeywords($path)
    {
        return $this->getSeoInformation($path, 'keywords');
    }

    /**
     * @param        $path
     * @param        $key
     * @param string $defaultValue
     *
     * @return string
     */
    protected function getSeoInformation($path, $key, $defaultValue = '')
    {
        // Lazy load the cache
        if ($this->aliasesCache === null) {
            $this->aliasesCache = $this->seoCache->getCache();
        }

        $path .= !preg_match('/\/$/', $path) ? '/' : '';

        return !empty($this->aliasesCache[$path][$key]) ? $this->aliasesCache[$path][$key] : $defaultValue;
    }

    /**
     * Gibt den Robots-Metatag der aktuell angezeigten Seite oder der
     * Elternseite zurück
     *
     * @return string
     */
    public function getPageRobotsSetting()
    {
        $robots = $this->getRobotsSetting($this->request->getUriWithoutPages());
        if (empty($robots)) {
            $robots = $this->getRobotsSetting($this->request->mod . '/' . $this->request->controller . '/' . $this->request->file);
        }
        if (empty($robots)) {
            $robots = $this->getRobotsSetting($this->request->mod);
        }

        return strtolower(!empty($robots) ? $robots : $this->getRobotsSetting());
    }

    /**
     * Gibt die jeweilige Einstellung für den Robots-Metatag zurück
     *
     * @param string $path
     *
     * @return string
     */
    public function getRobotsSetting($path = '')
    {
        $replace = [
            1 => 'index,follow',
            2 => 'index,nofollow',
            3 => 'noindex,follow',
            4 => 'noindex,nofollow',
        ];

        if ($path === '') {
            return strtr($this->seoConfig->getSettings()['robots'], $replace);
        } else {
            $robot = $this->getSeoInformation($path, 'robots', 0);

            if ($robot == 0) {
                $robot = $this->seoConfig->getSettings()['robots'];
            }

            return strtr($robot, $replace);
        }
    }

    /**
     * @param $string
     *
     * @return $this
     */
    public function setDescriptionPostfix($string)
    {
        $this->metaDescriptionPostfix = $string;

        return $this;
    }

    /**
     * Setzt die kanonische URI
     *
     * @param $path
     *
     * @return $this
     */
    public function setCanonicalUri($path)
    {
        $this->canonical = $path;

        return $this;
    }

    /**
     * Setzt die nächste Seite
     *
     * @param $path
     *
     * @return $this
     */
    public function setNextPage($path)
    {
        $this->nextPage = $path;

        return $this;
    }

    /**
     * Setzt die vorherige Seite
     *
     * @param $path
     *
     * @return $this
     */
    public function setPreviousPage($path)
    {
        $this->previousPage = $path;

        return $this;
    }

    /**
     * Gibt die Formularfelder für die Suchmaschinenoptimierung aus
     *
     * @param string $path
     *
     * @return array
     */
    public function formFields($path = '')
    {
        if (!empty($path)) {
            $path .= !preg_match('/\/$/', $path) ? '/' : '';

            $alias = isset($_POST['alias']) ? $_POST['alias'] : $this->aliases->getUriAlias($path, true);
            $keywords = isset($_POST['seo_keywords']) ? $_POST['seo_keywords'] : $this->getKeywords($path);
            $description = isset($_POST['seo_description']) ? $_POST['seo_description'] : $this->getDescription($path);
            $robots = $this->getSeoInformation($path, 'robots', 0);
        } else {
            $alias = $keywords = $description = '';
            $robots = 0;
        }

        $langRobots = [
            sprintf($this->lang->t('seo', 'robots_use_system_default'), $this->getRobotsSetting()),
            $this->lang->t('seo', 'robots_index_follow'),
            $this->lang->t('seo', 'robots_index_nofollow'),
            $this->lang->t('seo', 'robots_noindex_follow'),
            $this->lang->t('seo', 'robots_noindex_nofollow')
        ];

        return [
            'alias' => $alias,
            'keywords' => $keywords,
            'description' => $description,
            'robots' => $this->formsHelper->selectGenerator('seo_robots', [0, 1, 2, 3, 4], $langRobots, $robots)
        ];
    }

    /**
     * Löscht einen URI-Alias
     *
     * @param string $path
     *
     * @return boolean
     */
    public function deleteUriAlias($path)
    {
        $path .= !preg_match('/\/$/', $path) ? '/' : '';

        $bool = $this->seoModel->delete($path, 'uri');
        return $bool !== false && $this->seoCache->setCache() !== false;
    }

    /**
     * Trägt einen URI-Alias in die Datenbank ein bzw. aktualisiert den Eintrag
     *
     * @param string $path
     * @param string $alias
     * @param string $keywords
     * @param string $description
     * @param int    $robots
     *
     * @return boolean
     */
    public function insertUriAlias($path, $alias, $keywords = '', $description = '', $robots = 0)
    {
        $path .= !preg_match('/\/$/', $path) ? '/' : '';
        $keywords = Functions::strEncode($keywords);
        $description = Functions::strEncode($description);
        $values = [
            'alias' => $alias,
            'keywords' => $keywords,
            'description' => $description,
            'robots' => (int)$robots
        ];

        // Update an existing result
        if ($this->seoModel->uriAliasExists($path) === true) {
            $bool = $this->seoModel->update($values, ['uri' => $path]);
        } else {
            $values['uri'] = $path;
            $bool = $this->seoModel->insert($values);
        }

        return $bool !== false && $this->seoCache->setCache() !== false;
    }
}
