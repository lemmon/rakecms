<?php

namespace Rake;

class DataStack implements \Iterator, \ArrayAccess, \Countable
{
    private $_data;
    private $_keys;
    private $_i;
    private $_n;


    function __construct(array $data = [])
    {
        $this->_data = $data;
        $this->_keys = array_keys($data);
        $this->_i = 0;
        $this->_n = count($data);
    }


    function getFirst()
    {
        return $this->_data ? $this->offsetGet($this->_keys[0]) : NULL;
    }


    function getArray()
    {
        return $this->_data;
    }


    function getJson()
    {
        return json_encode($this->_data);
    }


    function shuffle()
    {
        $res = [];
        if ($keys = $this->_keys) {
            shuffle($keys);
            foreach ($keys as $key) {
                $res[$key] = $this->_data[$key];
            }
        }
        return new $this($res);
    }


    function filter($filters)
    {
        return new $this(array_filter(array_map(function($item) use ($filters) {
            foreach ($filters as $filter => $value) {
                if (!($item = _filter($item, explode('.', $filter), $value))) {
                    return FALSE;
                }
            }
            return $item;
        }, $this->_data)));
    }


    function count()
    {
        return $this->_n;
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


    function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_data);
    }


    function offsetGet($offset)
    {
        return is_array($res = $this->_data[$offset]) ? new self($res) : $res;
    }


    function offsetSet($offset, $value) {}
    function offsetUnset($offset) {}


    /*
    public function getIterator()
    {
        return $this->_data;
    }
    */
}