<?php

namespace Rake\Entity;

use Lemmon\DataStack;

class ContentStack extends DataStack
{


    function offsetGet($offset)
    {
        return new ContentChunk(...array_values(parent::getData()[$offset]));
    }


    function __toString()
    {
        return join("\n\n", array_filter(array_map(function($item) {
            return $item['text'];
        }, parent::getData())));
    }


    function __isset($name)
    {
        return boolval($this->{$name}->count());
    }


    function __get($name)
    {
        return new $this(array_filter(parent::getData(), function($item) use ($name) {
            return preg_match("/^{$name}(\..*)?$/", $item['name']);
        }));
    }


    function current()
    {
        return new ContentChunk(...array_values(parent::current(FALSE)));
    }
}