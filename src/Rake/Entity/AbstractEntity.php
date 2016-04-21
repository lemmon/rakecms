<?php

namespace Rake\Entity;

use Rake\Site;
use Rake\Template;
use function Rake\parse_content;

abstract class AbstractEntity implements \ArrayAccess
{
    private $_site;
    private $_item;


    function __construct(Site $site, $item)
    {
        $this->_site = $site;
        $this->_item = $item;
    }


    function getSite()
    {
        return $this->_site;
    }


    function getDir()
    {
        return dirname($this->getFile());
    }


    function getLink()
    {
        return $this->_item['link'];
    }


    /*
    function getPath()
    {
        return $this->_item['path'];
    }
    */


    function getFile()
    {
        return $this->_item['file'];
    }


    function getData()
    {
        return $this->_item['data'];
    }


    function getContent()
    {
        return parse_content(file_get_contents(BASE_DIR . '/content/' . $this->getFile()));
    }


    function getParent()
    {
        $tree = $this->_site->getTree($this->_item['l10n']);
        $link = $this->_item['link'];
        do {
            $link = rtrim(preg_replace('#[^/]+$#', '', $link), '/');
        } while ($link and !isset($tree[$link]));
        return $link ? $this->_site->getItem($link) : NULL;
    }


    function getType()
    {
        return substr($this->_item['type'], 1);
    }

    
    function getPage()
    {
        $res = $this;
        do {
            $res = $res->getParent();
        } while ($res and !($res instanceof Page));
        return $res;
    }


    function getBreadcrumbs()
    {
        $res = [];
        $page = $this;
        while ($page = $page->getParent()) {
            $res[] = $page;
        }
        return array_reverse($res);
    }


    function getLocale()
    {
        return $this->_site->getLocale($this->_item['l10n']);
    }


    function render(array $opt = [])
    {
        $tpl = new Template\Dispatcher($this, $opt);
        $opt = $this->_site->getOpt();
        
        if (isset($opt['template']) and $opt['template'] instanceof \Closure) {
            $opt['template']($tpl);
        }

        return $tpl->render($this->_item['data']['template'] ?? ($this->_item['type'] ? substr($this->_item['type'], 1, -1) : NULL) ?? 'default', []);
    }


    function getName()
    {
        return $this->_item['data']['name'] ?? $this->_item['name'];
    }


    function getCaption()
    {
        return @$this->_item['data']['caption'] ?: $this->getName();
    }


    function getHref()
    {
        return $this->_site->getRouter()->to(':page', ['link' => $this->_item['link']]);
    }


    function offsetExists($name)
    {
        return method_exists($this, 'get' . ucfirst($name)) || isset($this->_item['data'][$name]);
    }


    function offsetGet($name)
    {
        return method_exists($this, $_ = 'get' . ucfirst($name)) ? $this->{$_}() : $this->_item['data'][$name];
    }


    function offsetSet($offset, $value)
    {
    }


    function offsetUnset($offset)
    {
    }
}
