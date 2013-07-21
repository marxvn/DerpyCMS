<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\Http\Request;
use Slim\Slim;

class DerpyCMS extends \Slim\Slim
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
        try {
            static::$pdo = new \PDO(DERPY_DB_DSN, DERPY_DB_USER, DERPY_DB_PASS);
        } catch (\PDOException $e) {
            $this->halt(500, 'Unable to connect to database');
        }
    }

    /**
     * Initializes page routes from database
     *
     * @TODO Separate page content logic into \DerpyCMS\Models\Page instead of using directly here?
     * @TODO Create a route cache, no need to poll the DB every request
     * @TODO Properly resolve parent path using the slugs
     */
    public function init()
    {
        $app = $this->getInstance();
        $routes = new Routes();
        $routes = $routes->getPageRoutes();
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

    public static function getPDOInstance()
    {
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
                'templates.path' => DERPY_TPL_PATH,
                'view'           => '\DerpyCMS\Page',
                'blob.path'      => DERPY_BLOB_PATH,
                'cache.path'     => DERPY_CACHE_PATH,
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
        $this->view->appendData(Blob::getParts($id));
        $this->view->display($template);
    }

    public function __destruct()
    {
        $this->pdo = null;
    }
}