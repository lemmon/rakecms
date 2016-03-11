<?php

namespace Rake\Entity;

use Lemmon\DataStack;

class ContentChunk
{
    private $_name;
    private $_data;
    private $_text;


    function __construct($name, $data, $text)
    {
        $this->_name = $name;
        $this->_data = $data;
        $this->_text = $text;
    }


    function __toString()
    {
        return $this->_text;
    }


    function __isset($name)
    {
        return isset($this->_data[$name]);
    }


    function __get($name)
    {
        return is_array($_ = $this->_data[$name]) ? new DataStack($_) : $_;
    }
}