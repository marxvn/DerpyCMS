<?php
/**
 * DerpyCMS
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
		return true;
	}

	public function delete($key) {
		return true;
	}

	public function getHits() {
		return 0;
	}

	public function getMisses() {
		return 0;
	}

	public function close() {
		return true;
	}
}