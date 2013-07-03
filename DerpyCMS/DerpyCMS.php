<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use \Slim\Slim;

class DerpyCMS extends \Slim\Slim {
	private $pdo;

	public function __construct($userSettings = array()) {
		parent::__construct($userSettings = array());
		try {
			$this->pdo = new \PDO(DERPY_DB_DSN, DERPY_DB_USER, DERPY_DB_PASS);
		}
		catch (\PDOException $e) {
			$this->halt(500, 'Unable to connect to database');
		}
	}


	/**
	 * Get default application settings
	 * @return array
	 */
	public static function getDefaultSettings()
	{
		$slim_defaults = parent::getDefaultSettings();
		return array_merge($slim_defaults, array(
			'templates.path' => DERPY_TPL_PATH,
			'view' => '\DerpyCMS\Models\Page',
		));
	}


	public function __destruct() {
		$this->pdo = null;
	}
}