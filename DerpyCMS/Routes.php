<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */
namespace DerpyCMS;

use Slim\Http\Request;

class Routes
{
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
                    'SELECT id, parent_id, template_id, slug, request_method FROM '.DERPY_DB_PREFIX.'pages;'
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