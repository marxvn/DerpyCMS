<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\View;

class Page extends \Slim\View
{
    public function render($template)
    {
        $this->appendData(Blob::getParts($this->getData('id')));
        var_dump($this->data);
        die();
    }
}
