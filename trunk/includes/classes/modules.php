<?php
/**
 * Modules
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
/**
 * Klasse für die Module
 *
 * @author Goratsch Webdesign
 * @package ACP3
 * @subpackage Core
 */
class modules
{
	/**
	 * Array, welches die URI Parameter enthält
	 *
	 * @var array
	 * @access protected
	 */
	protected $params = array();
	/**
	 * Die komplette übergebene URL
	 *
	 * @var string
	 */
	public $query = '';

	/**
	 * Zerlegt u.a. die übergebenen Parameter in der URI in ihre Bestandteile
	 *
	 * @return modules
	 */
	function __construct()
	{
		$this->query = substr(str_replace(PHP_SELF, '', htmlentities($_SERVER['PHP_SELF'], ENT_QUOTES)), 1);

		if (!empty($this->query) && preg_match('/^(acp\/)/', $this->query)) {
			// Definieren, dass man sich im Administrationsbereich befindet
			define('IN_ADM', true);
			// "acp/" entfernen
			$this->query = substr($this->query, 4);
		} else {
			// Definieren, dass man sich im Frontend befindet
			define('IN_ACP3', true);
		}
		$query = !empty($this->query) ? explode('/', $this->query) : 0;
		$defaultModule = defined('IN_ADM') ? 'acp' : 'news';
		$defaultPage = defined('IN_ADM') ? 'adm_list' : 'list';

		$this->mod = !empty($query[0]) ? $query[0] : $defaultModule;
		$this->page = !empty($query[1]) ? $query[1] : $defaultPage;

		if (!empty($_POST['cat']))
			$this->params['cat'] = $_POST['cat'];
		if (!empty($_POST['action']))
			$this->params['action'] = $_POST['action'];

		if (!empty($query[2])) {
			$c_query = count($query);

			for ($i = 2; $i < $c_query; $i++) {
				if (!empty($query[$i])) {
					if (!defined('POS') && preg_match('/^(pos_(\d+))$/', $query[$i])) {
						define('POS', substr($query[$i], 4));
					} elseif (preg_match('/^(([a-z0-9-]+)_(.+))$/', $query[$i])) {
						$pos = strpos($query[$i], '_');
						$this->params[substr($query[$i], 0, $pos)] = substr($query[$i], $pos + 1);
					}
				}
			}
		}
		if (!defined('POS')) {
			define('POS', '0');
		}
	}
	/**
	 * Gibt alle URI Parameter aus
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (!empty($key) && array_key_exists($key, $this->params))
			return $this->params[$key];
		return null;
	}
	/**
	 * Überpüft, ob ein Modul überhaupt existiert, bzw. der Benutzer auf ein Modul Zugriff hat
	 *
	 * @param string $module
	 * 	Zu überprüfendes Modul
	 * @param string $page
	 * 	Zu überprüfende Moduldatei
	 * @return boolean
	 */
	public function check($module = 0, $page = 0) {
		global $auth, $db;
		static $access_level = array();

		$module = !empty($module) ? $module : $this->mod;
		$page = !empty($page) ? $page : $this->page;

		if (file_exists(ACP3_ROOT . 'modules/' . $module . '/' . $page . '.php')) {
			$xml = simplexml_load_file(ACP3_ROOT . 'modules/' . $module . '/module.xml');

			if ((string) $xml->info->active == '1') {
				// Falls die einzelnen Zugriffslevel auf die Module noch nicht gesetzt sind, diese aus der Datenbank selektieren
				if (!isset($access_level[$module])) {
					// Zugriffslevel für Gäste
					$access_id = 2;
					// Zugriffslevel für Benutzer holen
					if ($auth->isUser()) {
						$info = $auth->getUserInfo();
						if (!empty($info)) {
							$access_id = $info['access'];
						}
					}
					$access_to_modules = $db->select('modules', 'access', 'id = \'' . $access_id . '\'');
					$modules = explode(',', $access_to_modules[0]['modules']);

					foreach ($modules as $row) {
						$access_level[substr($row, 0, -2)] = substr($row, -1, 1);
					}
				}

				// XML Datei parsen
				foreach ($xml->access->item as $item) {
					if ((string) $item->file == $page && (string) $item->level != '0' && isset($access_level[$module]) && (string) $item->level <= $access_level[$module]) {
						return true;
					}
				}
			}
		}
		return false;
	}
	/**
	 * Gibt ein alphabetisch sortiertes Array mit allen gefundenen Modulen des ACP3 mitsamt Modulinformationen aus
	 *
	 * @return array
	 */
	public function modulesList()
	{
		$modules_dir = scandir(ACP3_ROOT . 'modules/');
		$mod_list = array();

		foreach ($modules_dir as $module) {
			$info = $this->parseInfo($module);
			if (is_array($info)) {
				$mod_list[$info['name']] = $info;
			}
		}
		ksort($mod_list);
		return $mod_list;
	}
	/**
	 * Gibt eine Seitenauswahl aus
	 *
	 * @param integer $rows
	 *  Anzahl der Datensätze
	 * @return string
	 *  Gibt die Seitenauswahl aus
	 */
	public function pagination($rows)
	{
		global $tpl;

		if ($rows > CONFIG_ENTRIES) {
			// Alle angegeben URL Parameter mit in die URL einbeziehen
			$acp = defined('IN_ADM') ? 'acp/' : '';
			$id = !empty($this->id) ? '/id_' . $this->id : '';
			$cat = !empty($this->cat) ? '/cat_' . $this->cat : '';
			$gen = '';
			if (!empty($this->gen)) {
				foreach ($this->gen as $key => $value) {
					if ($key != 'pos') {
						$gen .= '/' . $key . '_' . $value;
					}
				}
			}

			$tpl->assign('uri', uri($acp . $this->mod . '/' . $this->page . $id . $cat . $gen));

			// Seitenauswahl
			$c_pages = ceil($rows / CONFIG_ENTRIES);
			$recent = 0;

			for ($i = 1; $i <= $c_pages; $i++) {
				$pages[$i]['selected'] = POS == $recent ? true : false;
				$pages[$i]['page'] = $i;
				$pages[$i]['pos'] = 'pos_' . $recent . '/';

				$recent = $recent + CONFIG_ENTRIES;
			}
			$tpl->assign('pages', $pages);

			// Vorherige Seite
			$pos_prev = array('pos' => POS - CONFIG_ENTRIES >= 0 ? 'pos_' . (POS - CONFIG_ENTRIES) . '/' : '', 'selected' => POS == 0 ? true : false);
			$tpl->assign('pos_prev', $pos_prev);

			// Nächste Seite
			$pos_next = array('pos' => 'pos_' . (POS + CONFIG_ENTRIES) . '/', 'selected' => POS + CONFIG_ENTRIES >= $rows ? true : false);
			$tpl->assign('pos_next', $pos_next);

			return $tpl->fetch('common/pagination.html');
		}
	}
	/**
	 * Durchläuft für das angeforderte Modul den <info> Abschnitt in der
	 * module.xml und gibt die gefunden Informationen als Array zurück
	 *
	 * @param string $module
	 * @return mixed
	 */
	public function parseInfo($module)
	{
		$path = ACP3_ROOT . 'modules/' . $module . '/module.xml';
		if (!preg_match('=/=', $module) && is_file($path)) {
			$xml = simplexml_load_file($path);

			$info = $xml->info;

			$mod_info = array();
			$mod_info['dir'] = $module;
			$mod_info['author'] = (string) $info->author;
			$mod_info['description'] = (string) $info->description['lang'] == 'true' ? lang($module, 'mod_description') : (string) $info->description;
			$mod_info['name'] = (string) $info->name['lang'] == 'true' ? lang($module, $module) : (string) $info->name;
			$mod_info['version'] = (string) $info->version['core'] == 'true' ? CONFIG_VERSION : (string) $info->version;
			$mod_info['active'] = (string) $info->active;
			$mod_info['tables'] = !empty($info->tables) ? explode(',', (string) $info->tables) : false;
			$mod_info['categories'] = isset($info->categories) ? true : false;
			$mod_info['protected'] = $info->protected ? true : false;
			return $mod_info;
		}
		return false;
	}
}
?>