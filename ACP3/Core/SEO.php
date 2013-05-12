<?php
/**
 * SEO
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Core
 */

namespace ACP3\Core;

/**
 * Klasse zum Setzen von URI Aliases, Keywords und Beschreibungen für Seiten
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Core
 */
class SEO
{
	/**
	 * Caching Variable für die URI-Aliases
	 *
	 * @access private
	 * @var array
	 */
	private static $aliases = array();
	/**
	 * Gibt die nächste Seite an
	 * 
	 * @var string 
	 */
	private static $next_page = '';
	/**
	 * Gibt die vorherige Seite an
	 *
	 * @var string 
	 */
	private static $previous_page = '';
	/**
	 * Kanonische URL
	 *
	 * @var string
	 */
	private static $canonical = '';

	private static $meta_description_postfix = '';

	/**
	 * Setzt den Cache für die URI-Aliase
	 *
	 * @return boolean
	 */
	private static function setSEOCache()
	{
		$aliases = \ACP3\CMS::$injector['Db']->fetchAll('SELECT uri, alias, keywords, description, robots FROM ' . DB_PRE . 'seo');
		$c_aliases = count($aliases);
		$data = array();

		for ($i = 0; $i < $c_aliases; ++$i) {
			$data[$aliases[$i]['uri']] = array(
				'alias' => $aliases[$i]['alias'],
				'keywords' => $aliases[$i]['keywords'],
				'description' => $aliases[$i]['description'],
				'robots' => $aliases[$i]['robots']
			);
		}

		return Cache::create('aliases', $data, 'seo');
	}
	/**
	 * Gibt den Cache der URI-Aliase aus
	 *
	 * @return array
	 */
	private static function getSEOCache()
	{
		if (Cache::check('aliases', 'seo') === false)
			self::setSEOCache();

		return Cache::output('aliases', 'seo');
	}
	/**
	 * Gibt die für die jeweilige Seite gesetzten Metatags aus
	 *
	 * @return string 
	 */
	public static function getMetaTags()
	{
		$meta = array(
			'description' => defined('IN_ADM') === true ? '' : self::getPageDescription(),
			'keywords' => defined('IN_ADM') === true ? '' : self::getPageKeywords(),
			'robots' => defined('IN_ADM') === true ? 'noindex,nofollow' : self::getPageRobotsSetting(),
			'previous_page' => self::$previous_page,
			'next_page' => self::$next_page,
			'canonical' => self::$canonical,
		);
		\ACP3\CMS::$injector['View']->assign('meta', $meta);

		return \ACP3\CMS::$injector['View']->fetchTemplate('system/meta.tpl');
	}
	/**
	 * Gibt die Beschreibung der aktuell angezeigten Seite aus
	 *
	 * @return string
	 */
	public static function getPageDescription()
	{
		// Meta Description für die Homepage einer Website
		if (\ACP3\CMS::$injector['URI']->query === CONFIG_HOMEPAGE) {
			return CONFIG_SEO_META_DESCRIPTION !== '' ? CONFIG_SEO_META_DESCRIPTION : '';
		} else {
			$description = self::getDescription(\ACP3\CMS::$injector['URI']->getCleanQuery());
			if (empty($description))
				$description = self::getDescription(\ACP3\CMS::$injector['URI']->mod . '/' . \ACP3\CMS::$injector['URI']->file);

			return $description . (!empty($description) && !empty(self::$meta_description_postfix) ? ' - ' . self::$meta_description_postfix : '');
		}
	}
	/**
	 * Gibt die Keywords der aktuell angezeigten Seite oder der
	 * Elternseite aus
	 *
	 * @return string
	 */
	public static function getPageKeywords()
	{
		$keywords = self::getKeywords(\ACP3\CMS::$injector['URI']->getCleanQuery());
		if (empty($keywords))
			$keywords = self::getKeywords(\ACP3\CMS::$injector['URI']->mod . '/' . \ACP3\CMS::$injector['URI']->file);
		if (empty($keywords))
			$keywords = self::getKeywords(\ACP3\CMS::$injector['URI']->mod);

		return strtolower(!empty($keywords) ? $keywords : CONFIG_SEO_META_KEYWORDS);
	}
	/**
	 * Gibt den Robots-Metatag der aktuell angezeigten Seite oder der
	 * Elternseite aus
	 *
	 * @return string 
	 */
	public static function getPageRobotsSetting()
	{
		$robots = self::getRobotsSetting(\ACP3\CMS::$injector['URI']->getCleanQuery());
		if (empty($robots))
			$robots = self::getRobotsSetting(\ACP3\CMS::$injector['URI']->mod . '/' . \ACP3\CMS::$injector['URI']->file);
		if (empty($robots))
			$robots = self::getRobotsSetting(\ACP3\CMS::$injector['URI']->mod);

		return strtolower(!empty($robots) ? $robots : self::getRobotsSetting());
	}
	/**
	 * Gibt die Beschreibung der Seite aus
	 *
	 * @param string $path
	 * @return string
	 */
	public static function getDescription($path)
	{
		if (empty(self::$aliases))
			self::$aliases = self::getSEOCache();

		$path.= !preg_match('/\/$/', $path) ? '/' : '';

		return !empty(self::$aliases[$path]['description']) ? self::$aliases[$path]['description'] : '';
	}
	/**
	 * 
	 * @param string $string
	 */
	public  static function setDescriptionPostfix($string)
	{
		self::$meta_description_postfix = $string;
	}
	/**
	 * Gibt die Schlüsselwörter der Seite aus
	 *
	 * @param string $path
	 * @return string
	 */
	public static function getKeywords($path)
	{
		if (empty(self::$aliases))
			self::$aliases = self::getSEOCache();

		$path.= !preg_match('/\/$/', $path) ? '/' : '';

		return !empty(self::$aliases[$path]['keywords']) ? self::$aliases[$path]['keywords'] : '';
	}
	/**
	 * Gibt die jeweilige Einstellung für den Robots-Metatag aus
	 *
	 * @param string $path
	 * @return string 
	 */
	public static function getRobotsSetting($path = '')
	{
		$replace = array(
			1 => 'index,follow',
			2 => 'index,nofollow',
			3 => 'noindex,follow',
			4 => 'noindex,nofollow',
		);

		if ($path === '') {
			return strtr(CONFIG_SEO_ROBOTS, $replace);
		} else {
			if (empty(self::$aliases))
				self::$aliases = self::getSEOCache();

			$path.= !preg_match('/\/$/', $path) ? '/' : '';

			$robot = isset(self::$aliases[$path]) === false || self::$aliases[$path]['robots'] == 0 ? CONFIG_SEO_ROBOTS : self::$aliases[$path]['robots'];
			return strtr($robot, $replace);
		}
	}
	/**
	 * Gibt einen URI-Alias aus
	 *
	 * @param string $path
	 * @return string
	 */
	public static function getUriAlias($path, $for_form = false)
	{
		if (empty(self::$aliases))
			self::$aliases = self::getSEOCache();

		$path.= !preg_match('/\/$/', $path) ? '/' : '';

		return !empty(self::$aliases[$path]['alias']) ? self::$aliases[$path]['alias'] : ($for_form === true ? '' : $path);
	}
	/**
	 * Setzt die kanonische URI
	 *
	 * @param string $path
	 */
	public static function setCanonicalUri($path)
	{
		self::$canonical = $path;
	}
	/**
	 * Setzt die nächste Seite
	 *
	 * @param string $path 
	 */
	public static function setNextPage($path)
	{
		self::$next_page = $path;
	}
	/**
	 * Setzt die vorherige Seite
	 *
	 * @param string $path 
	 */
	public static function setPreviousPage($path)
	{
		self::$previous_page = $path;
	}
	/**
	 * Löscht einen URI-Alias
	 *
	 * @param string $alias
	 * @param string $path
	 * @return boolean
	 */
	public static function deleteUriAlias($path)
	{
		$path.= !preg_match('/\/$/', $path) ? '/' : '';

		$bool = \ACP3\CMS::$injector['Db']->delete(DB_PRE . 'seo', array('uri' => $path));
		$bool2 = self::setSEOCache();
		return $bool !== false && $bool2 !== false ? true : false;
	}
	/**
	 * Trägt einen URI-Alias in die Datenbank ein bzw. aktualisiert den Eintrag
	 *
	 * @param string $path
	 * @param string $alias
	 * @param string $keywords
	 * @param string $description
	 * @return boolean
	 */
	public static function insertUriAlias($path, $alias, $keywords = '', $description = '', $robots = 0)
	{
		$path.= !preg_match('/\/$/', $path) ? '/' : '';
		$keywords = str_encode($keywords);
		$description = str_encode($description);

		// Vorhandenen Alias aktualisieren
		if (\ACP3\CMS::$injector['Db']->fetchColumn('SELECT COUNT(*) FROM ' . DB_PRE . 'seo WHERE uri = ?', array($path)) == 1) {
			$bool = \ACP3\CMS::$injector['Db']->update(DB_PRE . 'seo', array('alias' => $alias, 'keywords' => $keywords, 'description' => $description, 'robots' => (int) $robots), array('uri' => $path));
		// Neuer Eintrag in DB
		} else {
			$bool = \ACP3\CMS::$injector['Db']->insert(DB_PRE . 'seo', array('alias' => $alias, 'uri' => $path, 'keywords' => $keywords, 'description' => $description, 'robots' => (int) $robots));
		}

		$bool2 = self::setSEOCache();
		return $bool !== false && $bool2 !== false ? true : false;
	}
	/**
	 * Gibt die Formularfelder für die Suchmaschinenoptimierung aus
	 *
	 * @param string $alias
	 * @param string $keywords
	 * @param string $description
	 * @param string $robots
	 * @return string
	 */
	public static function formFields($path = '')
	{
		if (!empty($path)) {
			$path.= !preg_match('/\/$/', $path) ? '/' : '';

			$alias = isset($_POST['alias']) ? $_POST['alias'] : self::getUriAlias($path, true);
			$keywords = isset($_POST['seo_keywords']) ? $_POST['seo_keywords'] : self::getKeywords($path);
			$description = isset($_POST['seo_description']) ? $_POST['seo_description'] : self::getDescription($path);
			$robots = isset(self::$aliases[$path]) === true ? self::$aliases[$path]['robots'] : 0;
		} else {
			$alias = $keywords = $description = '';
			$robots = 0;
		}

		$lang_robots = array(
			sprintf(\ACP3\CMS::$injector['Lang']->t('system', 'seo_robots_use_system_default'), self::getRobotsSetting()),
			\ACP3\CMS::$injector['Lang']->t('system', 'seo_robots_index_follow'),
			\ACP3\CMS::$injector['Lang']->t('system', 'seo_robots_index_nofollow'),
			\ACP3\CMS::$injector['Lang']->t('system', 'seo_robots_noindex_follow'),
			\ACP3\CMS::$injector['Lang']->t('system', 'seo_robots_noindex_nofollow')
		);
		$seo = array(
			'enable_uri_aliases' => (bool) CONFIG_SEO_ALIASES,
			'alias' => isset($alias) ? $alias : '',
			'keywords' => $keywords,
			'description' => $description,
			'robots' => Functions::selectGenerator('seo_robots', array(0, 1, 2, 3, 4), $lang_robots, $robots)
		);

		\ACP3\CMS::$injector['View']->assign('seo', $seo);
		return \ACP3\CMS::$injector['View']->fetchTemplate('system/seo_fields.tpl');
	}
	/**
	 * Überprüft, ob ein URI-Alias existiert
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function uriAliasExists($path)
	{
		if (empty(self::$aliases))
			self::$aliases = self::getSEOCache();

		$path.= !preg_match('/\/$/', $path) ? '/' : '';

		return array_key_exists($path, self::$aliases) === true && !empty(self::$aliases[$path]['alias']);
	}
}