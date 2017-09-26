<?php

namespace Rake\Entity;

class Post extends AbstractEntity
{


    function getAbstract()
    {
        $res = $this->getContent()[0];
        if ($i = strpos($res, '<!-- more -->')) {
            $res = substr($res, 0, $i);
        }
        return $res;
    }
}