<?php

namespace Rake;

use Lemmon\Router\Router;
use Lemmon\DataStack;

class Site
{
    private $_build;
    private $_env;
    private $_site;
    private $_router;
    private $_pages = [];


    function __construct(Router $router, array $o = NULL)
    {
        $this->_build = $o['build'] ?? FALSE;
        $this->_env = $o['env'] ?? 'default';
        $this->_site = json_decode(file_get_contents(BASE_DIR . '/build/site.json'), TRUE);
        $this->_router = $router;
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


    function getLocales()
    {
        return $this->_site['l10n'];
    }


    function getBuild()
    {
        return $this->_build;
    }


    function getEnv()
    {
        return $this->_env;
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


    function filter(array $filter)
    {
        return (new DataStack($this->_site['data']))->filter($filter);
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