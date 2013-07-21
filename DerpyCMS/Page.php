<?php
/**
 * DerpyCMS - A derped CMS built on Slim
 */

namespace DerpyCMS;

use Slim\View;

class Page extends \Slim\View
{
    public function content()
    {
        return $this->getData('parts.content');
    }

    public function title()
    {
        return $this->getData('page.title');
    }

    public function description()
    {
        return $this->getData('page.description');
    }

    public function keywords()
    {
        return $this->getData('page.keywords');
    }

    public function getPart($part)
    {
        $part = 'parts.'.$part;
        if (array_key_exists($part, $this->data)) {
            return $this->getData($part);
        } else {
            return '';
        }
    }
}
