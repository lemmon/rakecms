<?php

namespace Rake;

class Posts extends DataStack
{


    function getRecent($n = NULL)
    {
        $res = $this->getArray();
        if ($n) {
            $res = array_slice($res, 0, $n);
        }
        return new self($res);
    }
}