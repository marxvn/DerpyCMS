<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\Http\Request;
use Slim\Slim;
use \DerpyCMS\Models\Page;
use \DerpyCMS\Models\Cache;


// Path Helpers
define('DERPYCMS', DERPYCMS_BASE.'/DerpyCMS');
define('DERPYCMS_MODELS', DERPYCMS.'/Models');
define('DERPYCMS_DATA', DERPYCMS_BASE.'/Data');
define('DERPYCMS_TPLS', DERPYCMS_DATA.'/Templates');
define('DERPYCMS_VIEWS', DERPYCMS_DATA.'/Views');
define('DERPYCMS_CACHE', DERPYCMS_DATA.'/Cache');
define('DERPYCMS_BLOBS', DERPYCMS_DATA.'/Blobs');

if (!defined('DERPYCMS_DB_DSN')) {
	define('DERPYCMS_DB_DSN', 'mysql:dbname=derpycms;host=localhost;port=3306');
}
if (!defined('DERPYCMS_DB_USER')) {
	define('DERPYCMS_DB_USER', 'derpycms');
}
if (!defined('DERPYCMS_DB_PASS')) {
	define('DERPYCMS_DB_PASS', 'derpycms');
}
if (!defined('DERPY_DB_PREFIX')) {
	define('DERPY_DB_PREFIX', 'derpycms_');
}
if (!defined('DERPYCMS_CACHE_DSN')) {
	define('DERPYCMS_CACHE_DSN', 'file:'.DERPYCMS_CACHE);
}
if (!defined('DERPYCMS_BASE')) {
	define('DERPYCMS_BASE', dirname(dirname(dirname(__FILE__))));
}

class DerpyCMS extends Slim {
	/**
	 * @var \PDO
	 */
	static $pdo;

	/**
	 * @var \DerpyCMS\Models\Cache
	 */
	static $cache;

	/**
	 * Constructor
	 *
	 * @param  array $userSettings Associative array of application settings
	 */
	public function __construct($userSettings = array()) {
		parent::__construct($userSettings = array());
		$this->initRoutes();
		self::getPDOInstance();
		self::getCacheEngine();
	}

	/**
	 * Initializes page routes from database
	 *
	 * @return null
	 */
	public function initRoutes() {
		$app = $this->getInstance();
		$routes = Page::getRoutes();
		foreach ($routes as $route) {
			$callable = function() use ($app, $route) {
				$app->renderPage($route);
			};
			switch ($route->request_method) {
				case Request::METHOD_POST:
					$this->post($route->path, $callable);
					break;
				case Request::METHOD_DELETE:
					$this->delete($route->path, $callable);
					break;
				case Request::METHOD_PUT:
					$this->put($route->path, $callable);
					break;
				case Request::METHOD_OPTIONS:
					$this->options($route->path, $callable);
					break;
				default:
				case Request::METHOD_HEAD:
				case Request::METHOD_GET:
					$this->get($route->path, $callable);
					break;
			}
		}
	}

	/**
	 * Get active database connection
	 *
	 * @return \PDO
	 */
	public static function getPDOInstance() {
		if (!static::$pdo instanceof \PDO) {
			try {
				static::$pdo = new \PDO(DERPYCMS_DB_DSN, DERPYCMS_DB_USER, DERPYCMS_DB_PASS);
			} catch (\PDOException $e) {
				self::halt(500, 'Unable to connect to database: '.$e->getMessage());
			}
		}

		return static::$pdo;
	}

	/**
	 * Get active cache engine
	 *
	 * @return \DerpyCMS\Models\Cache
	 */
	public static function getCacheEngine() {
		if (!static::$cache instanceof Cache) {
			try {
				static::$cache = new Cache(DERPYCMS_CACHE_DSN);
			} catch (\Exception $e) {
				self::halt(500, 'Unable to initialize cache: '.$e->getMessage());
			}
		}

		return static::$cache;
	}

	/**
	 * Get default application settings
	 *
	 * @return array
	 */
	public static function getDefaultSettings() {
		$slim_defaults = parent::getDefaultSettings();

		return array_merge(
			$slim_defaults,
			array(
				'templates.path' => DERPYCMS_TPLS,
				//'view'           => '\DerpyCMS\Models\Page',
				'blob.path'      => DERPYCMS_BLOBS,
				'cache.path'     => DERPYCMS_CACHE,
			)
		);
	}

	/**
	 * Process a page request
	 * Call this method within a GET, POST, PUT, DELETE, NOT FOUND, or ERROR
	 * callable to render a template whose output is appended to the
	 * current HTTP response body. How the template is rendered is
	 * delegated to the current View.
	 *
	 * @param  object $route    The original route
	 * @param  int    $status   HTTP status for the page
	 */
	public function renderPage($route, $status = 200){
		if (!is_null($status)) {
			$this->response->status($status);
		}
		$page = new Models\Page($route->id);
		var_dump($page);
	}

	public function __destruct() {
		static::$pdo = null;
		static::$cache = null;
	}
}