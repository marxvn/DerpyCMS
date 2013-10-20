<?php
/**
 * Derpy-CMS
 * 
 * @author Diftraku
 */

namespace DerpyCMS\Cache;

interface Engine {
	public function get($key);

	public function set($key, $val, $time = 0);

	public function delete($key);

	public function getHits();

	public function getMisses();
}