<?php

namespace Rake;

class Posts extends DataStack
{


    function getRecent($a = NULL, $b = NULL)
    {
        $res = $this->getArray();
        // sort by date created
        usort($res, function($a, $b){
            $a = strtotime($a['created']);
            $b = strtotime($b['created']);
            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        });
        // slice
        if ($a) {
            $res = $b ? array_slice($res, $a, $b) : array_slice($res, 0, $a);
        }
        //
        return new self($res);
    }
}