<?php

namespace Rake\Entity;

class Post extends AbstractEntity
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