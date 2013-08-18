<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\View;
use Slim\Http\Request;

/**
 * Class Page
 *
 * @package DerpyCMS
 */
class Page extends View
{
    /**
     * Table name for data
     *
     * @const
     */
    const TABLE_NAME = 'page';
    /**
     * Table name for metadata
     *
     * @const
     */
    const TABLE_META_NAME = 'page_meta';

    /**
     * @var array Key based array of children
     */
    protected $children = array();

    /**
     * @return mixed
     */
    public function content()
    {
        return $this->getData('parts.content');
    }

    /**
     * @return mixed
     */
    public function title()
    {
        return $this->getData('page.title');
    }

    /**
     * @return mixed
     */
    public function description()
    {
        return $this->getData('page.description');
    }

    /**
     * @return mixed
     */
    public function keywords()
    {
        return $this->getData('page.keywords');
    }

    /**
     * @param $part
     * @return mixed|string
     */
    public function getPart($part)
    {
        $part = 'parts.'.$part;
        if (array_key_exists($part, $this->data)) {
            return $this->getData($part);
        } else {
            return '';
        }
    }

    /**
     * Get data
     *
     * @param  string|null $key
     * @param  string|null $default
     * @return mixed            If key is null, array of template data;
     *                          If key exists, value of datum with key;
     *                          If key does not exist, null;
     */
    public function getData($key = null, $default = null)
    {
        if (!is_null($key)) {
            return isset($this->data[$key]) ? $this->data[$key] : $default;
        } else {
            return $this->data;
        }
    }

    /**
     * Get parent page ID
     *
     * @return mixed
     */
    public function parentId()
    {
        return $this->getData('page.parent_id');
    }

    /**
     * Get specific child
     *
     * @param $index
     * @return object|bool  Child object if $index exists,
     *                      otherwise false
     */
    public function child($index)
    {
        $children = $this->children();
        if (array_key_exists($index, $children)) {
            return $children[$index];
        } else {
            return false;
        }
    }

    /**
     * Get page children
     *
     * @param int $level
     * @param int $page_id
     * @return array    Key based array of children,
     *                  last results are available through Page::$children
     */
    public function children($level = 1, $page_id = null)
    {
        if (is_null($page_id)) {
            $page_id = $this->getData('page.id');
        }
        $db = DerpyCMS::getPDOInstance();
        $query = $db->prepare(
            'SELECT * FROM '.DERPY_DB_PREFIX.Page::TABLE_NAME.' WHERE parent_id = :page_id'
        );
        $query->bindValue(':page_id', $page_id, \PDO::PARAM_INT);
        $query->execute();
        $children = $query->fetchAll(\PDO::FETCH_OBJ);
        if ($level > 1 && is_array($children)) {
            $level--;
            $sub_children = array();
            foreach ($children as $child) {
                $sub_children = array_merge($this->children($level, $child->id), $sub_children);
            }
            $children = array_merge($children, $sub_children);
        }
        if (!is_array($children)) {
            $children = array();
        }
        $this->children = $children;
        return $children;
    }


    /**
     * STATIC METHODS
     */

    /**
     * Retrieve page metadata
     *
     * @param int $page_id Page ID to retrieve metadata for
     * @return array An array of metadata arranged in page.key => meta pairs
     */
    public static function getMeta($page_id)
    {
        $db = DerpyCMS::getPDOInstance();
        $query = $db->prepare(
            'SELECT `key`, `val` FROM '.DERPY_DB_PREFIX.Page::TABLE_META_NAME.' WHERE page_id = :page_id'
        );
        $query->bindValue(':page_id', $page_id, \PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetchAll(\PDO::FETCH_OBJ);
        $meta = array();
        foreach ($data as $d) {
            $meta['page.'.$d->key] = $d->val;
        }
        $query = $db->prepare(
            'SELECT title, content_type FROM '.DERPY_DB_PREFIX.Page::TABLE_NAME.' WHERE id = :page_id'
        );
        $query->bindValue(':page_id', $page_id, \PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetch(\PDO::FETCH_OBJ);
        foreach ($data as $key => $val) {
            $meta['page.'.$key] = $val;
        }
        return $meta;
    }

    /**
     * Get routes for all pages in database
     *
     * @return array
     * @throws \Slim\Exception\Stop If database query fails
     */
    public static function getPageRoutes()
    {
        $db = DerpyCMS::getPDOInstance();
        $app = DerpyCMS::getInstance();
        $pages = $app->config('cache.path').'/pages.json';
        if (file_exists($pages)) {
            $pages = json_decode(file_get_contents($pages));
        } else {
            try {
                $query = $db->prepare(
                    'SELECT id, parent_id, template_id, slug, request_method FROM '.DERPY_DB_PREFIX.Page::TABLE_NAME.';'
                );
                $query->execute();
                $data = $query->fetchAll(\PDO::FETCH_OBJ);

                // Assign page IDs as index keys
                $pages = array();
                foreach ($data as $row) {
                    $pages[$row->id] = $row;
                }
                unset($data);

                foreach ($pages as $id => $page) {
                    // Resolve paths for pages
                    if ($page->id == 1) {
                        $path = '/';
                    } else {
                        $path = Page::resolvePath($page, $pages);
                        $path = implode('/', $path);
                    }
                    $page->path = $path;

                    // Make sure we have a request method
                    if (empty($page->request_method) || is_null($page->request_method)) {
                        $page->request_method = Request::METHOD_GET;
                    }

                    // And return it all back where we got it
                    $pages[$id] = $page;
                }

                // Prep data for cache
                $data = json_encode($pages);
                $h = fopen($app->config('cache.path').'/pages.json', 'w+');
                fwrite($h, $data);
                fclose($h);
                unset($data);
            } catch (\PDOException $e) {
                $app->halt(500, 'Database Failure: '.$e->getMessage());
            }
        }
        return $pages;
    }

    /**
     * @param       $page
     * @param array $pages
     * @return array
     */
    protected static function resolvePath($page, array $pages)
    {
        if (!is_null($page->parent_id) && array_key_exists($page->parent_id, $pages)) {
            $parent = $pages[$page->parent_id];
            $slug = array($page->slug);
            $parent_slug = array($parent->slug);
            if (!is_null($parent->parent_id)) {
                $parent_slug = self::resolvePath($parent, $pages);
            }
            $slug = array_merge($parent_slug, $slug);
            return $slug;
        } else {
            return $page->slug;
        }
    }
}
