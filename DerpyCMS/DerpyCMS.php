<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\Http\Request;
use Slim\Slim;


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
if (!defined('DERPYCMS_BASE')) {
	define('DERPYCMS_BASE', dirname(dirname(dirname(__FILE__))));
}
// Path Helpers
define('DERPYCMS',          DERPYCMS_BASE. '/DerpyCMS');
define('DERPYCMS_MODELS',   DERPYCMS.      '/Models');
define('DERPYCMS_DATA',     DERPYCMS_BASE. '/Data');
define('DERPYCMS_TPLS',     DERPYCMS_DATA. '/Templates');
define('DERPYCMS_VIEWS',    DERPYCMS_DATA. '/Views');
define('DERPYCMS_CACHE',    DERPYCMS_DATA. '/Cache');
define('DERPYCMS_BLOBS',    DERPYCMS_DATA. '/Blobs');

class DerpyCMS extends Slim
{
    /**
     * @var \PDO
     */
    static $pdo;

    /**
     * Constructor
     *
     * @param  array $userSettings Associative array of application settings
     */
    public function __construct($userSettings = array())
    {
        parent::__construct($userSettings = array());
        self::getPDOInstance();
    }

    /**
     * Initializes page routes from database
     *
     * @return null
     */
    public function init()
    {
        $app = $this->getInstance();
        $routes = Page::getPageRoutes();
        foreach ($routes as $route) {
            $callable = function () use ($app, $route) {
                $app->renderPage($route->template_id, $route->id);
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
    public static function getPDOInstance()
    {
        if (!static::$pdo instanceof \PDO) {
            try {
                static::$pdo = new \PDO(DERPYCMS_DB_DSN, DERPYCMS_DB_USER, DERPYCMS_DB_PASS);
            } catch (\PDOException $e) {
                DerpyCMS::halt(500, 'Unable to connect to database: '.$e->getMessage());
            }
        }
        return static::$pdo;
    }

    /**
     * Get default application settings
     *
     * @return array
     */
    public static function getDefaultSettings()
    {
        $slim_defaults = parent::getDefaultSettings();
        return array_merge(
            $slim_defaults,
            array(
                'templates.path' => DERPYCMS_TPLS,
                'view'           => '\DerpyCMS\Page',
                'blob.path'      => DERPYCMS_BLOBS,
                'cache.path'     => DERPYCMS_CACHE,
            )
        );
    }

    /**
     * Render a page
     * Call this method within a GET, POST, PUT, DELETE, NOT FOUND, or ERROR
     * callable to render a template whose output is appended to the
     * current HTTP response body. How the template is rendered is
     * delegated to the current View.
     *
     * @param  string $template The name of the template passed into the view's render() method
     * @param  int    $id       Page ID to render
     * @param  int    $status   The HTTP response status code to use (optional)
     */
    public function renderPage($template, $id, $status = null)
    {
        if (!is_null($status)) {
            $this->response->status($status);
        }
        $this->view->setTemplatesDirectory($this->config('templates.path'));
        $this->view->appendData(
            array_merge(
                Page::getMeta($id),
                Blob::getParts($id),
                array('page.id' => $id, 'page.template_id' => $template, 'response.status' => $status)
            )
        );
        $this->view->display($template);
    }

    public function __destruct()
    {
        static::$pdo = null;
    }
}