<?php

namespace Rake;

class Page implements \ArrayAccess
{
    private $_site;
    private $_page;
    private $_number;


    function __construct(Site $site, $page, $number = NULL)
    {
        $this->_site = $site;
        $this->_page = $page;
        $this->_number = $number;
    }


    function getSite()
    {
        return $this->_site;
    }


    function getDir()
    {
        return dirname($this->getFile());
    }


    function getFile()
    {
        return $this->_page['file'];
    }


    function getNumber()
    {
        return $this->_number ?: 1;
    }


    function getContent()
    {
        return trim(preg('/\-\-\-.*\-\-\-(.*)$/suU', file_get_contents(BASE_DIR . '/content/' . $this->getFile()))[1]);
    }


    function getLocale()
    {
        return $this->_site->getLocale($this->_page['l10n']);
    }


    function getTemplate()
    {
        return @$this->_page['data']['template'] ?: 'default';
    }


    function getName()
    {
        return @$this->_page['data']['name'] ?: $this->_page['name'];
    }


    function getCaption()
    {
        return @$this->_page['data']['caption'] ?: $this->getName();
    }


    function getHref()
    {
        return $this->_site->getRouter()->to($this->_page['href']);
    }


    function offsetExists($name)
    {
        return isset($this->_page['data'][$name]);
    }


    function offsetGet($name)
    {
        return $this->_page['data'][$name];
    }


    function offsetSet($offset, $value)
    {
    }


    function offsetUnset($offset)
    {
    }
}