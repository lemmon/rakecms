<?php

namespace Rake;

class DataStack implements \Iterator
{
    private $_data;
    private $_keys;
    private $_i;
    private $_n;


    function __construct(array $data)
    {
        $this->_data = $data;
        $this->_keys = array_keys($data);
        $this->_i = 0;
        $this->_n = count($data);
    }


    function getFirst()
    {
        return $this->_data[$this->_keys[0]];
    }


    function shuffle()
    {
        $keys = array_keys($this->_data);
        shuffle($keys);
        $res = [];
        foreach ($keys as $key) {
            $res[$key] = $this->_data[$key];
        }
        return new self($res);
    }


    function filter($filters) {
        return new self(array_filter(array_map(function($item) use ($filters) {
            foreach ($filters as $filter => $value) {
                if (!($item = _filter($item, explode('.', $filter), $value))) {
                    return FALSE;
                }
            }
            return $item;
        }, $this->_data)));
    }


    function rewind()
    {
        $this->_i = 0;
    }

    function current()
    {
        return $this->_data[$this->_keys[$this->_i]];
    }


    function key()
    {
        return $this->_keys[$this->_i];
        return $this->_keys[$this->_i];
    }


    function next()
    {
        ++$this->_i;
    }


    function valid() {
        return isset($this->_keys[$this->_i]);
    }


    /*
    public function getIterator()
    {
        return $this->_data;
    }
    */
}