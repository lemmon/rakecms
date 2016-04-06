<?php

namespace Rake\Entity;

use Lemmon\DataStack;

class ContentChunk implements \ArrayAccess
{
    private $_name;
    private $_data;
    private $_text;


    function __construct(string $name, array $data = NULL, string $text = NULL)
    {
        $this->_name = $name;
        $this->_data = $data;
        $this->_text = $text;
    }


    function getName()
    {
        return $this->_name;
    }


    function getData()
    {
        return $this->_data;
    }


    function getText()
    {
        return $this->_text;
    }


    function __toString()
    {
        return $this->_text;
    }


    function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }


    function offsetGet($offset)
    {
        return is_array($_ = $this->_data[$offset]) ? new DataStack($_) : $_;
    }


    function offsetSet($offset, $value) {}
    function offsetUnset($offset) {}
}