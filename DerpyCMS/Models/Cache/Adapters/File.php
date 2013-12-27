<?php
/**
 * DerpyCMS
 *
 * @author Diftraku
 */

namespace DerpyCMS\Models\Cache\Adapters;

use DerpyCMS\DerpyCMS;
use DerpyCMS\Models\Cache\Engine as CacheEngine;

class File implements CacheEngine {
	protected $hits = 0;
	protected $misses = 0;
	protected $path;

	public function __construct($args) {
		if (preg_match("/^(.+)(?:;|)/", $args, $matches)) $this->path = $matches[1];
		if (preg_match("/gz=([^;](?:true|false))/", $args, $matches)) $gzip = $matches[1];
		if (preg_match("/gz_level=([^;][0-9])/", $args, $matches)) $gzip_level = $matches[1];
	}

	public function get($key) {
		assert(!is_null($key));
		$file = $this->getStoragePath($key);
		if (file_exists($file)) {
			$data = file($file);
			if (count($data) == 2) {
				$expiry = $data[0];
				$val = $data[1];
				if (!empty($val) && ($expiry >= time())) {
					$this->hits++;
					return unserialize($val);
				}
			}
		}
		$this->misses++;
		return false;
	}

	public function set($key, $val, $time = 0) {
		assert(!is_null($key));
		$file = $this->getStoragePath($key);
		if (!is_dir(dirname($file))) {
			mkdir(dirname($file), 0775, true);
		}
		if ($time == 0) {
			$expiry = strtotime('+'.DerpyCMS::getInstance()->config('cache.lifetime'));
		}
		else {
			$expiry = time()+$time;
		}
		$data = $expiry."\n".serialize($val);
		file_put_contents($file, $data);
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

	public function close() {
		return true;
	}


}
