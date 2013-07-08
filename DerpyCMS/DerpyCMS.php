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
     * @TODO Separate page content logic into \DerpyCMS\Models\Page instead of using directly here
     * @TODO Create a route cache, no need to poll the DB every request
     * @TODO Properly resolve parent path using the slugs
     */
    public function init()
    {
        $db = $this->pdo;
        try {
            $query = $db->prepare(
                'SELECT id, parent_id, slug, request_method, content_blob_id, content_file FROM '.DERPY_DB_PREFIX.'pages;'
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
                    $this->get(
                        $page->slug,
                        function () use ($page, $db) {
                            if (!empty($page->content_blob_id)) {
                                $query = $db->prepare('SELECT `blob` FROM '.DERPY_DB_PREFIX.'blobs WHERE id = :id ;');
                                $query->bindValue(':id', (int)$page->content_blob_id, \PDO::PARAM_INT);
                                $query->execute();
                                $blob = $query->fetchObject();
                                echo $blob->blob;
                            } else {
                                if (!empty($page->content_file)) {

                                }
                            }
                        }
                    );
                }
                /*if (!empty($page->content_blob_id)) {
                    $query = $db->prepare('SELECT `blob` FROM '.DERPY_DB_PREFIX.'blobs WHERE id = :id ;');
                    $query->bindValue(':id', (int)$page->content_blob_id, \PDO::PARAM_INT);
                    $query->execute();
                    $blob = $query->fetchObject();
                }
                else if (!empty($page->content_file)) {

                }*/
            }
        } catch (\PDOException $e) {

        }
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
            )
        );
    }


    public function __destruct()
    {
        $this->pdo = null;
    }
}