<?php

namespace Rake;

class DataStack implements \Iterator
{
    private $_data;
    private $_i;
    private $_n;


    function __construct(array $data)
    {
        $this->_data = array_values($data);
        $this->_i = 0;
        $this->_n = count($data);
    }


    function getFirst()
    {
        return $this->_data[0];
    }


    function rewind()
    {
        $this->_i = 0;
    }

    function current()
    {
        return $this->_data[$this->_i];
    }


    function key()
    {
        return $this->_i;
    }


    function next()
    {
        ++$this->_i;
    }


    function valid() {
        return isset($this->_data[$this->_i]);
    }


    /*
    public function getIterator()
    {
        return $this->_data;
    }
    */
}