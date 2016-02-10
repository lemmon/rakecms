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
            $obj = __NAMESPACE__ . '\\Entity\\' . (isset($res['type']) ? ucfirst(substr($res['type'], 1, -1)) : 'Page');
            return $this->_pages[$path] = new $obj($this, $res, $number);
        }
    }


    function getLocale($id)
    {
        return $this->_site['l10n'][$id];
    }


    function query($what, $locale_id, $mask = '*')
    {
        $mask = strtr($mask, ['**' => '.*', '*' => '[^/]*']);
        $tree = array_intersect($this->_site['tree'][$locale_id], $this->_site[$what][$locale_id]);
        $res = [];
        foreach ($tree as $link => $path) {
            if (preg_match("#^{$mask}$#", $link)) {
                $res[] = $this->getItem($path);
            }
        }
        return $res;
    }


    function getTree($locale_id)
    {
        return $this->_site['tree'][$locale_id];
    }


    function getRouter()
    {
        return $this->_router;
    }
}