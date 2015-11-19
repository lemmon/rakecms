<?php

namespace Rake;

use Symfony\Component\Yaml\Yaml;

class Data implements \ArrayAccess
{
    private $_page;
    private $_base;


    function __construct(AbstractItem $page)
    {
        $this->_page = $page;
        $this->_base = [
            BASE_DIR . '/content/',
            BASE_DIR . '/content/' . $page->getLocale()['dir'],
            BASE_DIR . '/content/' . $page->getDir(),
        ];
    }


    function offsetExists($offset)
    {
        return file_exists($this->_base[0] . '/' . $offset . '.yml')
            or file_exists($this->_base[1] . '/' . $offset . '.yml')
            or file_exists($this->_base[2] . '/' . $offset . '.yml')
        ;
    }


    function offsetGet($offset)
    {
        return new DataStack(array_replace_recursive(
            file_exists($this->_base[0] . '/' . $offset . '.yml') ? Yaml::parse(file_get_contents($this->_base[0] . '/' . $offset . '.yml')) : [],
            file_exists($this->_base[2] . '/' . $offset . '.yml') ? Yaml::parse(file_get_contents($this->_base[2] . '/' . $offset . '.yml'))
                : (file_exists($this->_base[1] . '/' . $offset . '.yml') ? Yaml::parse(file_get_contents($this->_base[1] . '/' . $offset . '.yml')) : [])
        ));
    }


    function offsetSet($offset, $value) {}
    function offsetUnset($offset) {}
}