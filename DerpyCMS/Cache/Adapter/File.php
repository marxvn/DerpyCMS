<?php
/**
 * Derpy-CMS
 * 
 * @author Diftraku
 */

namespace DerpyCMS\Cache\Adapter;

use DerpyCMS\Cache\Engine as CacheEngine;

class File implements CacheEngine {
	protected $hits = 0;
	protected $misses = 0;
	protected $path;

	public function __construct($args) {
		if(preg_match("/gz=([^;](?:true|false))/", $args, $matches)) $gzip = $matches[1];
		if(preg_match("/gz_level=([^;][0-9])/", $args, $matches)) $gzip_level = $matches[1];
	}

	public function get($key) {
		assert(!is_null($key));
		$val = file_get_contents($this->getStoragePath($key));
		if ($val) {
			$this->hits++;
			return $val;
		}
		else {
			$this->misses++;
			return false;
		}
	}

	public function set($key, $val, $time = 0) {
		assert(!is_null($key));
		file_put_contents($this->getStoragePath($key), $val);
	}

	public function delete($key) {
		assert(!is_null($key));
		unlink($this->getStoragePath($key));
	}

	public function getHits() {
		return $this->hits;
	}

	public function getMisses() {
		return $this->misses;
	}
	public function getStoragePath($key) {
		assert(!is_null($key));
		$key = sha1($key);
		$ab = substr($key, 0, 2);
		$cd = substr($key, 2, 2);
		return $this->path.DIRECTORY_SEPARATOR.$ab.DIRECTORY_SEPARATOR.$cd.DIRECTORY_SEPARATOR.$key;

	}
}
