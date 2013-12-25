<?php
/**
 * DerpyCMS - Cache
 * Blatantly copied from Shimmie's codebase
 * This stuff just works™
 *
 * @author Diftraku
 */

namespace DerpyCMS\Models;

use \DerpyCMS\Models\Cache\Adapters\APC;
use \DerpyCMS\Models\Cache\Adapters\File;
use \DerpyCMS\Models\Cache\Adapters\Memcache;
use \DerpyCMS\Models\Cache\Adapters\NoCache;
use \DerpyCMS\Models\Cache\Engine as CacheEngine;

class Cache implements CacheEngine {

	/**
	 * Active instance of a cache engine
	 *
	 * @var CacheEngine
	 */
	private $engine;

	public function __construct($dsn = 'file:./cache') {
		$matches = array();
		if (preg_match("#(memcache|apc|file):(.*)#", $dsn, $matches)) {
			switch ($matches[1]) {
				case 'memcache':
					$this->engine = new Memcache($matches[2]);
					break;
				case 'apc':
					$this->engine = new APC($matches[2]);
					break;
				case 'file':
					$this->engine = new File($matches[2]);
					break;
				case 'nocache':
				default:
					$this->engine = new NoCache();
					break;
			}
		}
		else {
			$this->engine = new NoCache();
		}
	}

	public function get($key) {
		return $this->engine->get($key);
	}

	public function set($key, $val, $time = 0) {
		return $this->engine->set($key, $val, $time = 0);
	}

	public function delete($key) {
		return $this->engine->delete($key);
	}

	public function getHits() {
		return $this->engine->getHits();
	}

	public function getMisses() {
		return $this->engine->getMisses();
	}

	public function close() {
		$this->engine->close();
	}

	function __destruct() {
		$this->close();
	}
}