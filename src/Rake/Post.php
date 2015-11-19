<?php

namespace Rake;

class Post extends Page
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