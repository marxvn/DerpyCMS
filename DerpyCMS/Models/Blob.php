<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS\Models;

use \DerpyCMS\DerpyCMS;
use \DerpyCMS\Model;

class Blob extends Model
{
	const TABLE = 'blobs';

	const TABLE_DATA = 'blob_data';

	/**
	 * @return mixed
	 */
	public function fetch() {
		$db = DerpyCMS::getPDOInstance();
		$query = $db->prepare('
			SELECT * FROM '.DERPY_DB_PREFIX.Blob::TABLE.' WHERE `hash` = :hash;
		');
		$query->execute(array('hash' => $this->get('id')));
		$blob = $query->fetch(\PDO::FETCH_ASSOC);
		if (intval($blob['file']) === 0) {
			// Blob is stored in DB
			$blob['data'] = self::getBlob($this->get('id'));
		} else {
			// Blob is stored in filesystem
			$blob['data'] = self::getFileBlob($this->get('id'));
		}
		$this->data = array_merge($this->data, $blob);
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
     * Fetches blobs stored as files
     * Based on Shimmie's warehouse_path()
     *
     * @param string $blob Hash of the blob
     * @return mixed
     */
    static public function getFileBlob($blob)
    {
        $app = DerpyCMS::getInstance();
        $base = $app->config('blob.path');
        $ab = substr($blob, 0, 2);
        $cd = substr($blob, 2, 2);
	    $file = $base.'/'.$ab.'/'.$cd.'/'.$blob;
	    if (file_exists($file)) {
		    return file_get_contents($file);
	    }
	    return '';
    }

	static public function getBlob($hash) {
		$db = DerpyCMS::getPDOInstance();
		$query = $db->prepare(
			'SELECT CONVERT(data USING utf8) FROM '.DERPY_DB_PREFIX.self::TABLE_DATA.' WHERE hash = :hash ;'
		);
		$query->execute(array('hash' => $hash));
		if ($data = $query->fetch()) {
			return $data[0];
		}
		return true;
	}


}