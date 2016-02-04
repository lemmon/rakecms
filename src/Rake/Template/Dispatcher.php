<?php

namespace Rake\Template;

use Rake\Router;

class Dispatcher
{
    private $_router;
    private $_template;


    function __construct(Router $router, array $o = [])
    {
        $this->_router = $router;
        $this->_template = new \Twig_Environment(new \Twig_Loader_Filesystem(BASE_DIR . '/templates'), array_replace([
            'cache' => BASE_DIR . '/cache/tpl',
            'auto_reload' => TRUE,
        ], $o));
        $this->_template->addExtension(new Extension($this->_router));
    }


    function render($name, $data = [])
    {
        return $this->_template->render("{$name}.twig.html", $data);
    }
}