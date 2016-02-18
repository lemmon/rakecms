<?php

namespace Rake\Template;

use Rake\Site as Site;

class Dispatcher
{
    private $_site;
    private $_template;


    function __construct(Site $site, array $o = [])
    {
        $this->_site = $site;
        $this->_template = new \Twig_Environment(new \Twig_Loader_Filesystem(BASE_DIR . '/src/templates'), array_replace([
            'cache' => BASE_DIR . '/cache/tpl',
            'auto_reload' => TRUE,
        ], $o));
        $this->_template->addExtension(new Extension($this->_site));
    }


    function render($name, $data = [])
    {
        return $this->_template->render($name . '.html', $data);
    }
}