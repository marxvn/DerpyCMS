<?php
/**
 * Derpy-CMS
 * 
 * @author Diftraku
 */

namespace DerpyCMS\Cache\Adapter;

use DerpyCMS\Cache\Engine as CacheEngine;

class NoCache implements CacheEngine {
	public function get($key) {
		return false;
	}

	public function set($key, $val, $time = 0) {
	}

	public function delete($key) {
	}

	public function getHits() {
		return 0;
	}

	public function getMisses() {
		return 0;
	}
}