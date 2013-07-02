<?php
/**
 * Derpy CMS - A derped CMS built on Slim
 */

namespace DerpyCMS;
class DerpyCMS {
	private $slim;
	private $pdo;

	public function __construct(array $config = array()) {
		require_once DERPY_BASE.'/config.php';
		$this->slim = new \Slim\Slim();
		$this->pdo = new \PDO(DERPY_DB_DSN, DERPY_DB_USER, DERPY_DB_PASS);
	}
}