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


    function query(array $filters)
    {
        return new DataStack(array_filter(array_map(function($item) use ($filters) {
            foreach ($filters as $filter => $value) {
                if (!_filter($item->getData(), explode('.', $filter), $value)) {
                    return FALSE;
                }
            }
            return $item;
        }, $this->_site->queryPages($this->_page->getLocale()['id'], '**'))));
    }
}
