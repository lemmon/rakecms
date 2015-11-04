<?php

namespace Rake;

class Site
{
    static private $_pages = [];
    
    private $_site;


    function __construct(Router $router)
    {
        $this->_router = $router;
        $this->_site = json_decode(file_get_contents(BASE_DIR . '/build/site.json'), TRUE);
    }


    function getPage($path, $number = NULL)
    {
        if (!array_key_exists($path, $this->_site['data'])) {
            throw new HttpNotFoundException;
        }
        return array_key_exists($path, self::$_pages) ? self::$_pages[$path] : self::$_pages[$path] = new Page($this, $this->_site['data'][$path], $number);
    }


    function getLocale($id)
    {
        return $this->_site['l10n'][$id];
    }


    function queryPages($locale_id, $mask = '*')
    {
        $mask = str_replace('**', '.+', $mask);
        $mask = str_replace('*', '[^/]*', $mask);
        $res = [];
        foreach ($this->_site['tree'][$locale_id] as $item) {
            if (preg_match("#^/$mask$#", $item)) {
                $res[] = $this->getPage($item);
            }
        }
        return $res;
    }


    function getRouter()
    {
        return $this->_router;
    }
}