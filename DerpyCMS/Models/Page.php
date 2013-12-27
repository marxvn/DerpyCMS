<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS\Models;

use \Slim\View;
use \Slim\Http\Request;
use \DerpyCMS\DerpyCMS;
use \DerpyCMS\Model;
use \DerpyCMS\Models\Blob;

/**
 * Class Page
 *
 * @package DerpyCMS
 */
class Page extends Model
{
    /**
     * Table name for data
     *
     * @const
     */
    const TABLE = 'pages';
    /**
     * Table name for metadata
     *
     * @const
     */
    const TABLE_META = 'page_meta';

	/**
	 * @return mixed
	 */
	public function fetch() {
		$db = DerpyCMS::getPDOInstance();
		$query = $db->prepare('
			SELECT * FROM '.DERPY_DB_PREFIX.self::TABLE.' WHERE id = :page_id;
			SELECT  `key`, val FROM '.DERPY_DB_PREFIX.self::TABLE_META.' WHERE '.DERPY_DB_PREFIX.self::TABLE_META.'.page_id =  :page_id;
			SELECT `hash`, `type` FROM '.DERPY_DB_PREFIX.Blob::TABLE.' WHERE page_id = :page_id;
		');
		$query->execute(array('page_id' => $this->get('id')));
		$data = $query->fetch(\PDO::FETCH_ASSOC);
		$query->nextRowset();
		$meta = $query->fetchAll(\PDO::FETCH_ASSOC);
		$query->nextRowset();
		$blobs = $query->fetchAll(\PDO::FETCH_ASSOC);

		$tmp = array();
		foreach ($meta as $kv_pair) {
			$tmp['meta.'.$kv_pair['key']] = $kv_pair['val'];
		}
		foreach ($blobs as $blob) {
			$tmp['parts.'.$blob['type']] = new Blob($blob['hash']);
		}
		foreach ($data as $key => $val) {
			$tmp['page.'.$key] = $val;
		}
		$this->data = array_merge($this->data, $tmp);
		// Housekeeping
		unset($data, $meta, $blobs, $tmp, $query);
		DerpyCMS::getCacheEngine()->set('page.'.$this->get('id'), $this);
	}

	/**
	 * @return mixed
	 */
	public function save() {
		// TODO: Implement save() method.
	}

	/**
	 * @return mixed
	 */
	public function destroy() {
		// TODO: Implement destroy() method.
	}

	/**
	 * Get routes for all pages in database
	 *
	 * @return array
	 * @throws \Slim\Exception\Stop If database query fails
	 */
	public static function getRoutes()
	{
		$db = DerpyCMS::getPDOInstance();
		$app = DerpyCMS::getInstance();
		$pages = $app->config('cache.path').'/pages.json';
		if (file_exists($pages)) {
			$pages = json_decode(file_get_contents($pages));
		} else {
			try {
				$query = $db->prepare(
					'SELECT id, parent_id, template_id, slug, request_method FROM '.DERPY_DB_PREFIX.self::TABLE.';'
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
						$path = self::resolvePath($page, $pages);
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
