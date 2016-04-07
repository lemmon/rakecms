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
    private $_template;
    private $_pages = [];


    function __construct($env = NULL, array $opt = [], int $build = NULL)
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


    function getRouter(array $o = NULL)
    {
        if ($o) {
            return new Router(array_replace_recursive($this->_opt['router'] ?? [], $o));
        } else {
            return $this->_router ?? $this->_router = new Router($this->_opt['router'] ?? []);
        }
    }


    function getTemplate(array $o = [])
    {
        if (!$o and $this->_template) {
            return $this->_template;
        }
        $t = new Template\Dispatcher($this, $o);
        if (isset($this->_opt['template']) and $this->_opt['template'] instanceof \Closure) {
            $this->_opt['template']($t);
        }
        return $o ? $t : $this->_template = $t;
    }
}