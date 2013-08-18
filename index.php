<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

define('DERPYCMS_BASE', dirname(__FILE__));
require_once DERPYCMS_BASE.'/config.php';
require_once DERPYCMS_BASE.'/vendor/autoload.php';

$app = new \DerpyCMS\DerpyCMS();
$app->init();
$app->run();
