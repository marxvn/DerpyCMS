<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\View;

class Page extends \Slim\View
{
    public function render($id)
    {
        $parts = Blob::getParts($id);
    }
}
