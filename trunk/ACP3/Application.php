<?php

namespace ACP3;
use ACP3\Core\Modules\Controller;
use ACP3\Core\SEO;

/**
 * Front Controller of the CMS
 *
 * @author Tino Goratsch
 */
class Application
{

    /**
     * Führt alle nötigen Schritte aus, um die Seite anzuzeigen
     */
    public static function run()
    {
        self::defineDirConstants();
        self::startupChecks();
        self::includeAutoLoader();
        self::initializeClasses();
        self::outputPage();
    }

    /**
     * Überprüft, ob die config.php existiert
     */
    public static function startupChecks()
    {
        // Standardzeitzone festlegen
        date_default_timezone_set('UTC');

        // DB-Config des ACP3 laden
        $path = ACP3_DIR . 'config.php';
        if (is_file($path) === false || filesize($path) === 0) {
            exit('The ACP3 is not correctly installed. Please navigate to the <a href="' . ROOT_DIR . 'installation/">installation wizard</a> and follow its instructions.');
            // Wenn alles okay ist, config.php einbinden und error_reporting setzen
        } else {
            require_once ACP3_DIR . 'config.php';

            // Wenn der DEBUG Modus aktiv ist, Fehler ausgeben
            error_reporting(defined('DEBUG') === true && DEBUG === true ? E_ALL : 0);
        }
    }

    /**
     * Einige Pfadkonstanten definieren
     */
    public static function defineDirConstants()
    {
        define('PHP_SELF', htmlentities($_SERVER['SCRIPT_NAME']));
        $php_self = dirname(PHP_SELF);
        define('ROOT_DIR', $php_self !== '/' ? $php_self . '/' : '/');
        define('ACP3_DIR', ACP3_ROOT_DIR . 'ACP3/');
        define('CLASSES_DIR', ACP3_DIR . 'Core/');
        define('MODULES_DIR', ACP3_DIR . 'Modules/');
        define('LIBRARIES_DIR', ACP3_ROOT_DIR . 'libraries/');
        define('VENDOR_DIR', ACP3_ROOT_DIR . 'vendor/');
        define('UPLOADS_DIR', ACP3_ROOT_DIR . 'uploads/');
        define('CACHE_DIR', UPLOADS_DIR . 'cache/');
    }

    /**
     * Klassen Autoloader inkludieren
     */
    public static function includeAutoLoader()
    {
        require VENDOR_DIR . 'autoload.php';
    }

    /**
     * Überprüfen, ob der Wartungsmodus aktiv ist
     */
    public static function checkForMaintenanceMode()
    {
        if ((bool)CONFIG_MAINTENANCE_MODE === true &&
            (defined('IN_ADM') === false && strpos(Core\Registry::get('URI')->query, 'users/login/') !== 0)
        ) {
            Core\Registry::get('View')->assign('PAGE_TITLE', CONFIG_SEO_TITLE);
            Core\Registry::get('View')->assign('CONTENT', CONFIG_MAINTENANCE_MESSAGE);
            Core\Registry::get('View')->displayTemplate('system/maintenance.tpl');
            exit;
        }
    }

    /**
     * Initialisieren der anderen Klassen
     */
    public static function initializeClasses()
    {
        $config = new \Doctrine\DBAL\Configuration();
        $connectionParams = array(
            'dbname' => CONFIG_DB_NAME,
            'user' => CONFIG_DB_USER,
            'password' => CONFIG_DB_PASSWORD,
            'host' => CONFIG_DB_HOST,
            'driver' => 'pdo_mysql',
            'charset' => 'utf8'
        );
        Core\Registry::set('Db', \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config));
        define('DB_PRE', CONFIG_DB_PRE);

        // Sytemeinstellungen laden
        Core\Config::getSystemSettings();

        // Pfade zum Theme setzen
        define('DESIGN_PATH', ROOT_DIR . 'designs/' . CONFIG_DESIGN . '/');
        define('DESIGN_PATH_INTERNAL', ACP3_ROOT_DIR . 'designs/' . CONFIG_DESIGN . '/');

        // Restliche Klassen instanziieren
        Core\Registry::set('View', new Core\View());

        Core\Registry::set('URI', new Core\URI(Core\Registry::get('Db')));

        Core\Registry::set('Session', new Core\Session(
            Core\Registry::get('Db'),
            Core\Registry::get('URI'),
            Core\Registry::get('View')
        ));

        Core\Registry::set('Auth', new Core\Auth(
            Core\Registry::get('Db'),
            Core\Registry::get('Session')
        ));

        Core\Registry::set('Lang', new Core\Lang(Core\Registry::get('Auth')));

        Core\Registry::set('SEO', new Core\SEO(
            Core\Registry::get('Lang'),
            Core\Registry::get('URI'),
            Core\Registry::get('View')
        ));

        Core\Registry::set('Date', new Core\Date(
            Core\Registry::get('Auth'),
            Core\Registry::get('Lang'),
            Core\Registry::get('View')
        ));

        Core\Registry::set('Breadcrumb', new Core\Breadcrumb(
            Core\Registry::get('Db'),
            Core\Registry::get('Lang'),
            Core\Registry::get('URI'),
            Core\Registry::get('View')
        ));

        Core\View::factory('Smarty');

        Core\ACL::initialize(Core\Registry::get('Auth')->getUserId());
    }

    /**
     * Gibt die Seite aus
     */
    public static function outputPage()
    {
        $view = Core\Registry::get('View');
        $uri = Core\Registry::get('URI');

        // Einige Template Variablen setzen
        $view->assign('PHP_SELF', PHP_SELF);
        $view->assign('REQUEST_URI', htmlentities($_SERVER['REQUEST_URI']));
        $view->assign('ROOT_DIR', ROOT_DIR);
        $view->assign('DESIGN_PATH', DESIGN_PATH);
        $view->assign('UA_IS_MOBILE', Core\Functions::isMobileBrowser());
        $view->assign('IN_ADM', defined('IN_ADM') ? true : false);

        $lang_info = Core\XML::parseXmlFile(ACP3_ROOT_DIR . 'languages/' . Core\Registry::get('Lang')->getLanguage() . '/info.xml', '/language');
        $view->assign('LANG_DIRECTION', isset($lang_info['direction']) ? $lang_info['direction'] : 'ltr');
        $view->assign('LANG', CONFIG_LANG);

        self::checkForMaintenanceMode();

        // Aktuelle Datensatzposition bestimmen
        define('POS', Core\Validate::isNumber($uri->page) && $uri->page >= 1 ? (int)($uri->page - 1) * Core\Registry::get('Auth')->entries : 0);

        if (defined('IN_ADM') === true && Core\Registry::get('Auth')->isUser() === false && $uri->query !== 'users/login/') {
            $redirect_uri = base64_encode('acp/' . $uri->query);
            $uri->redirect('users/login/redirect_' . $redirect_uri);
        }

        if (Core\Modules::hasPermission($uri->mod, $uri->file) === true) {
            $module = ucfirst($uri->mod);
            $section = defined('IN_ADM') === true ? 'Admin' : 'Frontend';
            $className = "\\ACP3\\Modules\\" . $module . "\\Controller\\" . $section;
            $action = 'action' . preg_replace('/(\s+)/', '', ucwords(strtolower(str_replace('_', ' ', defined('IN_ADM') === true ? substr($uri->file, 4) : $uri->file))));

            // Modul einbinden
            /** @var Controller $mod */
            $mod = new $className(
                Core\Registry::get('Auth'),
                Core\Registry::get('Breadcrumb'),
                Core\Registry::get('Date'),
                Core\Registry::get('Db'),
                Core\Registry::get('Lang'),
                Core\Registry::get('Session'),
                Core\Registry::get('URI'),
                Core\Registry::get('View'),
                Core\Registry::get('SEO')
            );
            $mod->$action();
            $mod->display();
        } else {
            $uri->redirect('errors/404');
        }
    }

}