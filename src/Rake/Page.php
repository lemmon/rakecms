<?php

namespace Rake;

class Page extends AbstractItem
{


    function getPage()
    {
        return $this;
    }


    function getChildren()
    {
        return new Pages($this->getSite()->query("@pages", $this->getLocale()['id'], "{$this->getPath()}/*"));
    }
}