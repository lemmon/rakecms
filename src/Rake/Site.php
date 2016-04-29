<?php

namespace Rake;

use Lemmon\Router\Router;
use Lemmon\DataStack;

class Site
{
    private $_env;
    private $_opt;
    private $_site;
    private $_build;
    private $_router;
    private $_entities = [];


    function __construct(string $env = NULL, array $opt = [], int $build = NULL)
    {
        // environment
        if (empty($env)) {
            $env = 'default';
        }
        // options
        if (file_exists($_file = BASE_DIR . '/scrapefile.yml')) {
            $_opt = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($_file));
        }
        if (file_exists($_file = BASE_DIR . '/scrapefile.php')) {
            $_opt = include $_file;
        }
        if (isset($_opt)) {
            $opt = array_replace_recursive($opt, $_opt['*'] ?? [], $_opt['all'] ?? [], $_opt[$env] ?? []);
        }
        //
        $this->_env = $env;
        $this->_opt = $opt ?? [];
        $this->_site = json_decode(file_get_contents(BASE_DIR . '/build/site.json'), TRUE);
        $this->_build = $build;
    }


    function dispatch(Callable $callback, array $opt = [])
    {
        $this->_router = new Router(array_replace_recursive($this->_opt['router'] ?? [], $opt['router'] ?? []));
        $this->_router->match('({link=index}.html)', ['link' => '[\w\-/]+'], function($r, string $link) use ($callback) {
            if ($page = $this->getItem($link)) {
                $callback($this, $page);
            } else {
                return FALSE;
            }
        }, 'page');
        if (!$this->_router->dispatch()) {
            throw new HttpNotFoundException;
        }
    }


    function getOpt()
    {
        return $this->_opt;
    }


    function getRouter()
    {
        return $this->_router;
    }


    function getItem($link)
    {
        if (!array_key_exists($link, $this->_site['data'])) {
            return FALSE;
        }
        if (array_key_exists($link, $this->_entities)) {
            return $this->_entities[$link];
        } else {
            $res = $this->_site['data'][$link];
            $obj = __NAMESPACE__ . '\\Entity\\' . (isset($res['type']) ? ucfirst(substr($res['type'], 1, -1)) : 'Page');
            return $this->_entities[$link] = new $obj($this, $res);
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
                $res[] = $this->getItem($link);
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
}