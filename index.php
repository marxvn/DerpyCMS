<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

define('DERPY_BASE', dirname(__FILE__));
require_once DERPY_BASE.'/config.php';
require_once DERPY_BASE.'/vendor/autoload.php';

$app = new \DerpyCMS\DerpyCMS();

// De Magicks!
$app->run();
