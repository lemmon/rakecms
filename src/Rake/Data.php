<?php

namespace Rake;

use Symfony\Component\Yaml\Yaml;

class Data implements \ArrayAccess
{
    private $_page;
    private $_base;


    function __construct(Page $page)
    {
        $this->_page = $page;
        $this->_base = BASE_DIR . '/content/' . $page->getDir();
    }


    function offsetExists($offset)
    {
        return file_exists(BASE_DIR . '/data/' . $offset . '.yml') or file_exists($this->_base . '/' . $offset . '.yml');
    }


    function offsetGet($offset)
    {
        return array_replace_recursive(
            file_exists(BASE_DIR . '/data/' . $offset . '.yml') ? Yaml::parse(file_get_contents(BASE_DIR . '/data/' . $offset . '.yml')) : [],
            file_exists($this->_base . '/' . $offset . '.yml') ? Yaml::parse(file_get_contents($this->_base . '/' . $offset . '.yml')) : []
        );
    }


    function offsetSet($offset, $value)
    {
    }


    function offsetUnset($offset)
    {
    }
}