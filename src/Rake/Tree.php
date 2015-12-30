<?php

namespace Rake;

class Tree
{
    private $_site;
    private $_page;


    function __construct($page)
    {
        $this->_site = $page->getSite();
        $this->_page = $page;
    }


    function getLocale()
    {
        return $this->_page;
    }


    function getPages($query = '*')
    {
        return new DataStack($this->_site->query('tree', $this->_page->getLocale()['id'], $query));
    }


    function __isset($what)
    {
        return NULL !== $this->__get($what);
    }


    function __get($what)
    {
        $class = __NAMESPACE__ . '\\' . ucwords($what);
        return new $class($this->_site->query("@{$what}", $this->_page->getLocale()['id'], '**'));
    }


    function query(array $filters)
    {
        return $this->getPages('**')->filter($filters);
    }
}
