<?php
/**
 * Derpy-CMS - Cache
 * Blatantly copied from Shimmie's codebase
 * This stuff just works™
 *
 * @author Diftraku
 */

namespace DerpyCMS;

use DerpyCMS\Cache\Adapter\APC;
use DerpyCMS\Cache\Adapter\File;
use DerpyCMS\Cache\Adapter\Memcache;
use DerpyCMS\Cache\Adapter\NoCache;
use DerpyCMS\Cache\Engine as CacheEngine;


class Cache implements CacheEngine {

	/**
	 * Active instance of a cache engine
	 *
	 * @var CacheEngine
	 */
	static private $engine;

	public function init($dsn = 'file://./cache/') {
		$matches = array();
		if (preg_match("#(memcache|apc|file)://(.*)#", $dsn, $matches)) {
			switch ($matches[1]) {
				case 'memcache':
					Cache::$engine = new Memcache($matches[2]);
					break;
				case 'apc':
					Cache::$engine = new APC($matches[2]);
					break;
				case 'file':
					Cache::$engine = new File($matches[2]);
					break;
			}
		}
		else {
			Cache::$engine = new NoCache();
		}
	}

	public function get($key) {
		return Cache::$engine->get($key);
	}

	public function set($key, $val, $time = 0) {
		return Cache::$engine->set($key, $val, $time = 0);
	}

	public function delete($key) {
		return Cache::$engine->delete($key);
	}

	public function getHits() {
		return Cache::$engine->getHits();
	}

	public function getMisses() {
		return Cache::$engine->getMisses();
	}

}