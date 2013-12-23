<?php
/**
 * DerpyCMS
 *
 * @author Diftraku
 */

namespace DerpyCMS\Cache\Adapter;

use DerpyCMS\Cache\Engine as CacheEngine;

class Memcache implements CacheEngine {

	/**
	 * @var Memcache
	 */
	protected $memcache;
	protected $hits = 0;
	protected $misses = 0;

	public function __construct($args) {
		$hp = explode(":", $args);
		if (class_exists("Memcache")) {
			$this->memcache = new \Memcache($args);
			@$this->memcache->pconnect($hp[0], $hp[1]);
		}
	}

	public function get($key) {
		assert(!is_null($key));
		$val = $this->memcache->get($key);
		if ($val !== false) {
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
		$this->memcache->set($key, $val, false, $time);
	}

	public function delete($key) {
		assert(!is_null($key));
		$this->memcache->delete($key);
	}

	public function getHits() {
		return $this->hits;
	}

	public function getMisses() {
		return $this->misses;
	}

	public function close() {
		$this->memcache->close();
		$this->memcache = null;
	}
}
