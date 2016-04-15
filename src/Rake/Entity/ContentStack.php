<?php

namespace Rake\Entity;

use Lemmon\DataStack;

class ContentStack extends DataStack
{


    function offsetExists($offset)
    {
        return is_int($offset)
            ? parent::offsetExists($offset)
            : (isset(parent::getData()[0]) ? parent::getData()[0]->offsetExists($offset) : FALSE)
            ;
    }


    function offsetGet($offset)
    {
        return is_int($offset)
            ? parent::getData()[$offset]
            : (isset(parent::getData()[0]) ? parent::getData()[0][$offset] : NULL)
            ;
    }


    function __toString()
    {
        return $this->getText();
    }


    function __isset($name)
    {
        return boolval($this->{$name}->count());
    }


    function __get($name)
    {
        return new $this(array_filter(parent::getData(), function($item) use ($name) {
            return $item->getName() == $name;
        }), $name);
    }


    function current()
    {
        return parent::current();
    }


    function getName()
    {
        return $this->count() ? $this[0]->getName() : NULL;
    }


    function getData()
    {
        return $this->count() ? $this[0]->getData() : NULL;
    }


    function getText()
    {
        return join("\n\n", array_filter(array_map(function($item) {
            return $item->getText();
        }, parent::getData())));
    }
}