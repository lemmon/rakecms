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


    function getPages()
    {
        return $this->_site->queryPages($this->_page->getLocale()['id'], '*');
    }
}