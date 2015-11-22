<?php

namespace Rake;

class Site
{
    private $_site;
    private $_router;
    private $_pages = [];


    function __construct(Router $router)
    {
        $this->_router = $router;
        $this->_site = json_decode(file_get_contents(BASE_DIR . '/build/site.json'), TRUE);
    }


    function getItem($path, $number = NULL)
    {
        if (!array_key_exists($path, $this->_site['data'])) {
            throw new HttpNotFoundException;
        }
        if (array_key_exists($path, $this->_pages)) {
            return $this->_pages[$path];
        } else {
            $res = $this->_site['data'][$path];
            $obj = __NAMESPACE__ . '\\' . (isset($res['type']) ? ucfirst(substr($res['type'], 1, -1)) : 'Page');
            return $this->_pages[$path] = new $obj($this, $res, $number);
        }
    }


    function getLocale($id)
    {
        return $this->_site['l10n'][$id];
    }


    function query($what, $locale_id, $mask = '*')
    {
        $mask = str_replace('**', '.+', $mask);
        $mask = str_replace('*', '[^/]*', $mask);
        $res = [];
        foreach ($this->_site[$what][$locale_id] as $item) {
            if (preg_match("#^/$mask$#", $item)) {
                $res[] = $this->getItem($item);
            }
        }
        return $res;
    }


    function getRouter()
    {
        return $this->_router;
    }
}