<?php

namespace Rake\Template;

use Rake\Site as Site;

class Dispatcher
{
    private $_site;
    private $_template;


    function __construct(Site $site, array $o = [], $filesystem = NULL)
    {
        $this->_site = $site;
        $this->_template = new \Twig_Environment(new \Twig_Loader_Filesystem($filesystem ?? BASE_DIR . '/src/templates'), $_ = array_replace([
            'cache' => BASE_DIR . '/cache/tpl',
            'auto_reload' => TRUE,
        ], $o));
        $this->_template->addExtension(new Extension($this->_site));
    }


    function getTemplate()
    {
        return $this->_template;
    }


    function render($name, $data = [])
    {
        return $this->_template->render($name . '.html', $data);
    }
}