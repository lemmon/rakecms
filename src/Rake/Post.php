<?php

namespace Rake;

class Post extends AbstractItem
{


    function getAbstract()
    {
        $res = $this->getContent();
        if ($i = strpos($res, '<!-- more -->')) {
            $res = substr($res, 0, $i);
        }
        return $res;
    }
}