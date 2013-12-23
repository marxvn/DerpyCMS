<?php
/**
 * DerpyCMS
 *
 * @author Diftraku
 */

namespace DerpyCMS\Cache\Adapter;

use DerpyCMS\Cache\Engine as CacheEngine;

class APC implements CacheEngine {
	protected $hits = 0;
	protected $misses = 0;

	public function __construct($args) {
	}

	public function get($key) {
		assert(!is_null($key));
		$val = apc_fetch($key);
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
		apc_store($key, $val, $time);
	}

	public function delete($key) {
		assert(!is_null($key));
		apc_delete($key);
	}

	public function getHits() {
		return $this->hits;
	}

	public function getMisses() {
		return $this->misses;
	}

	public function close() {
		return true;
	}
}
