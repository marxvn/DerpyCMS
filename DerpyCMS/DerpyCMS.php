<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use \Slim\Slim;

class DerpyCMS extends \Slim\Slim
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * Constructor
     *
     * @param  array $userSettings Associative array of application settings
     */
    public function __construct($userSettings = array())
    {
        parent::__construct($userSettings = array());
        try {
            $this->pdo = new \PDO(DERPY_DB_DSN, DERPY_DB_USER, DERPY_DB_PASS);
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
        $db = $this->pdo;
        try {
            $query = $db->prepare(
                'SELECT id, parent_id, template_id, slug, request_method, content_file FROM '.DERPY_DB_PREFIX.'pages;'
            );
            $query->execute();
            $pages = $query->fetchAll(\PDO::FETCH_OBJ);
            foreach ($pages as $page) {
                if (empty($page->request_method)) {
                    // @TODO this needs to be recursive
                    if (!empty($page->parent_id)) {
                        $query = $db->prepare('SELECT slug FROM '.DERPY_DB_PREFIX.'pages WHERE id = :parent_id;');
                        $query->bindValue(':parent_id', (int)$page->parent_id, \PDO::PARAM_INT);
                        $parent = $query->fetchObject();
                        $page->slug = $parent->slug.'/'.$page->slug;
                    }
                    // Index is The One
                    if ($page->id == 1) {
                        $page->slug = '/';
                    }
                    $app = $this;
                    $this->get(
                        $page->slug,
                        function () use ($app, $page) {
                            $app->preRender($page);
                        }
                    );
                }
            }
        } catch (\PDOException $e) {

        }
    }

    /**
     * Pre-Render data
     *
     * Retrieves all applicable data for the requested page and passes them onto \Slim::render()
     *
     * @todo Implement page caching (complete and partial static, partial dynamic and partial static)
     * @todo Move this somewhere else?
     * @todo Implement the logic inside a separate model? (Like WolfCMS does with Page extending Record)
     * @param  object $page Object containing page data from \DerpyCMS::init()
     */
    public function preRender($page)
    {
        $db = $this->pdo;
        // Gather all blobs
        $query = $db->prepare('SELECT id, `blob`, `file`, type FROM '.DERPY_DB_PREFIX.'blobs WHERE page_id = :page_id ;');
        $query->bindValue(':page_id', (int)$page->id, \PDO::PARAM_INT);
        $query->execute();
        $blobs = $query->fetchAll();
        $parts = array();
        foreach ($blobs as $blob) {
            if (!empty($blob->blob)) {
                // Blob is stored in DB
                $parts[$blob->type] = $blob->blob;
            } else {
                if (!empty($blob->file)) {
                    // Blob is stored in filesystem
                    $parts[$blob->type] = file_get_contents($this->getFileBlob($blob->file));
                }
                else {
                    // Invalid blob
                    // @TODO Die quietly?
                    $this->halt(500, 'Blob with id `'.$blob->id.'` for page_id `'.$page->id.'` is invalid and cannot be used for rendering.');
                }
            }
        }
        // Get template
    }

    /**
     * Fetches blobs stored as files
     * Based on Shimmie's warehouse_path()
     *
     * @todo Move to independent model instead clutter here?
     * @param string $blob Hash of the blob
     */
    public function getFileBlob($blob)
    {
        $base = $this->config('blob.path');
        $ab = substr($blob, 0, 2);
        $cd = substr($blob, 2, 2);
        return file_get_contents($base.'/'.$ab.'/'.$cd.'/'.$blob);
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
                'view'           => '\DerpyCMS\Models\Page',
                'blob.path'      => DERPY_CMS_BASE.'/Blobs',
            )
        );
    }


    public function __destruct()
    {
        $this->pdo = null;
    }
}