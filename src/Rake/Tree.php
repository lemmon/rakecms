<?php

namespace Rake;

class Tree
{
    private $_page;
    private $_site;
    private $_tree;


    function __construct($page)
    {
        $this->_page = $page;
        $this->_tree = $page->getSite()->getTree($this->_page->getLocale()['id']);
    }


    function getLocale()
    {
        return $this->_page;
    }


    function __isset($what)
    {
        return NULL !== $this->__get($what);
    }


    function __get($what)
    {
        return $this->_query($what, '**');
    }


    function __call($what, $args)
    {
        return $this->_query($what, ...$args);
    }


    private function _query($what, ...$args)
    {
        $class = __NAMESPACE__ . '\\Entity\\' . ucwords($what);
        return new $class($this->_page->getSite()->query("@{$what}", $this->_page->getLocale()['id'], ...$args));
    }


    /*
    function query(array $filters)
    {
        return $this->pages('**')->filter($filters);
    }
    */
}
