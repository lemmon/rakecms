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
        return $this->_site->query('tree', $this->_page->getLocale()['id'], '*');
    }


    function getPosts()
    {
        return new Posts($this->_site->query('@posts', $this->_page->getLocale()['id'], '**'));
    }


    function query(array $filters)
    {
        return (new DataStack($this->_site->query('tree', $this->_page->getLocale()['id'], '**')))->filter($filters);
    }
}
