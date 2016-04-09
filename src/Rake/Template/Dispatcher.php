<?php

namespace Rake\Template;

use Rake\Site;
use Rake\Entity\AbstractEntity;

class Dispatcher
{
    private $_template;


    function __construct(AbstractEntity $page, array $opt = [], $filesystem = NULL)
    {
        $t = new \Twig_Environment(new \Twig_Loader_Filesystem($filesystem ?? BASE_DIR . '/src/templates'), array_replace([
            'cache' => BASE_DIR . '/cache/tpl',
            'auto_reload' => TRUE,
        ], $opt));
        $t->addGlobal('link', $page->getSite()->getRouter());
        $t->addGlobal('site', $page->getSite());
        $t->addGlobal('page', $page->getPage());
        $t->addGlobal('tree', new \Rake\Tree($page));
        $t->addGlobal('data', new \Rake\Data($page));
        $t->addGlobal('i18n', new \Rake\I18n($page));
        $t->addGlobal('entry', $page);
        $t->addGlobal('content', $page->getContent());
        $t->addExtension(new Extension($page->getSite()));
        $this->_template = $t;
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