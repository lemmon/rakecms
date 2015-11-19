<?php

namespace Rake;

abstract class AbstractItem implements \ArrayAccess
{
    private $_site;
    private $_item;
    private $_number;


    function __construct(Site $site, $item, $number = NULL)
    {
        $this->_site = $site;
        $this->_item = $item;
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
        return $this->_item['file'];
    }


    function getNumber()
    {
        return $this->_number ?: 1;
    }


    function getData()
    {
        return $this->_item['data'];
    }


    function getContent()
    {
        return trim(preg('/\-\-\-.*\-\-\-(.*)$/suU', file_get_contents(BASE_DIR . '/content/' . $this->getFile()))[1]);
    }


    function getParent()
    {
        if ($path = $this->_item['path']) {
            return $this->_site->getItem('/' . rtrim(preg_replace('#[^/]+$#', '', $this->_item['path']), '/'));
        }
    }
    
    
    function getPage()
    {
        $res = $this;
        do {
            $res = $res->getParent();
        } while ($res and !($res instanceof Page));
        return $res;
    }


    function getLocale()
    {
        return $this->_site->getLocale($this->_item['l10n']);
    }


    function getTemplate()
    {
        return @reset(array_filter([
            @$this->_item['data']['template'],
            @$this->_item['type'] ? substr($this->_item['type'], 1, -1) : NULL,
            'default',
        ]));
    }


    function getName()
    {
        return @$this->_item['data']['name'] ?: $this->_item['name'];
    }


    function getCaption()
    {
        return @$this->_item['data']['caption'] ?: $this->getName();
    }


    function getHref()
    {
        return $this->_site->getRouter()->to($this->_item['href']);
    }


    function offsetExists($name)
    {
        return isset($this->_item['data'][$name]);
    }


    function offsetGet($name)
    {
        return method_exists($this, $_ = 'get' . ucfirst($name)) ? $this->{$_}() : $this->_item['data'][$name];
    }


    function offsetSet($offset, $value)
    {
    }


    function offsetUnset($offset)
    {
    }
}
