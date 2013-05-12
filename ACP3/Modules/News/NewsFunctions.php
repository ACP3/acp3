<?php
/**
 * News
 *
 * @author Tino Goratsch
 * @package ACP3
 * @subpackage Modules
 */

namespace ACP3\Modules\News;

use ACP3\Core;

/**
 * Stellt einige Helperfunktionen bereit
 */
class NewsFunctions {

	/**
	 * Erstellt den Cache einer News anhand der angegebenen ID
	 *
	 * @param integer $id
	 *  Die ID der News
	 * @return boolean
	 */
	public static function setNewsCache($id)
	{
		$data = ACP3\CMS::$injector['Db']->fetchAssoc('SELECT id, start, title, text, readmore, comments, category_id, uri, target, link_title FROM ' . DB_PRE . 'news WHERE id = ?', array($id));
		return Core\Cache::create('details_id_' . $id, $data, 'news');
	}

	/**
	 * Bindet die gecachete News ein
	 *
	 * @param integer $id
	 *  Die ID der News
	 * @return array
	 */
	public static function getNewsCache($id)
	{
		if (Core\Cache::check('details_id_' . $id, 'news') === false)
			self::setNewsCache($id);

		return Core\Cache::output('details_id_' . $id, 'news');
	}

}
