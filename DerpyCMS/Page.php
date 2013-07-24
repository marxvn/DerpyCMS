<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\View;
use Slim\Http\Request;

class Page extends \Slim\View
{
    const TABLE_NAME = 'page';
    const TABLE_META_NAME = 'page_meta';

    public function content()
    {
        return $this->getData('parts.content');
    }

    public function title()
    {
        return $this->getData('page.title');
    }

    public function description()
    {
        return $this->getData('page.description');
    }

    public function keywords()
    {
        return $this->getData('page.keywords');
    }

    public function getPart($part)
    {
        $part = 'parts.'.$part;
        if (array_key_exists($part, $this->data)) {
            return $this->getData($part);
        } else {
            return '';
        }
    }

    public function parentId()
    {
        return $this->getData('page.parent_id');
    }

    public function child($key)
    {

    }

    public function children()
    {

    }

    /**
     * Retrieve page metadata
     *
     * @param int $page_id Page ID to retrieve metadata for
     * @return array An array of metadata arranged in page.key => meta pairs
     */
    static public function getMeta($page_id)
    {
        $db = DerpyCMS::getPDOInstance();
        $query = $db->prepare(
            'SELECT `key`, `val` FROM '.DERPY_DB_PREFIX.Page::TABLE_META_NAME.' WHERE page_id = :page_id'
        );
        $query->bindValue(':page_id', $page_id, \PDO::PARAM_INT);
        $query->execute();
        $data = $query->fetchAll(\PDO::FETCH_OBJ);
        $meta = array();
        var_dump($data);
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
        var_dump($meta, $data);
        return $meta;
    }

    public function getPageRoutes()
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
                        $path = $this->resolvePath($page, $pages);
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
                $app->halt(500, 'We dun halt!');
            }
        }
        return $pages;
    }

    protected function resolvePath($page, array $pages)
    {
        if (!is_null($page->parent_id) && array_key_exists($page->parent_id, $pages)) {
            $parent = $pages[$page->parent_id];
            $slug = array($page->slug);
            $parent_slug = array($parent->slug);
            if (!is_null($parent->parent_id)) {
                $parent_slug = $this->resolvePath($parent, $pages);
            }
            $slug = array_merge($parent_slug, $slug);
            return $slug;
        } else {
            return $page->slug;
        }
    }
}
