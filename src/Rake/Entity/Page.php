<?php

namespace Rake\Entity;

class Page extends AbstractEntity
{


    function getPage()
    {
        return $this;
    }


    function getLevel()
    {
        return count(explode('/', parent::getLink())) - 1;
    }


    function getChildren()
    {
        return new Pages($this->getSite()->query("@pages", $this->getLocale()['id'], "{$this->getLink()}/*"));
    }


    function getSiblings()
    {
        return new Pages($this->getSite()->query("@pages", $this->getLocale()['id'], preg_replace('#[^/]+$#', '*', $this->getLink())));
    }
}