<?php

namespace Rake\Entity;

use Rake\Site;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractEntity implements \ArrayAccess
{
    private $_site;
    private $_item;
    private $_number;


    function __construct(Site $site, $item, $number = NULL)
    {
        $this->_site = $site;
        $this->_item = $item;
        $this->_number = $number;
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


    function getFile()
    {
        return $this->_item['file'];
    }


    function getNumber()
    {
        return $this->_number ?: 1;
    }


    function getData()
    {
        return $this->_item['data'];
    }


    function getContent()
    {
        $c = trim(file_get_contents(BASE_DIR . '/' . $this->getFile()));
		$c = preg_replace('{\r\n?}', "\n", $c);
        $c = trim(\Rake\preg('/^---(.*)---\h*\n(.*)$/usU', $c)[2] ?? '');
        
        $res = [];
        foreach (preg_split('/\n{2,}(?=---\h+\w+\h+---\h*\n)/', $c) as $i => $item) {
            $item = trim($item);
            preg_match('/^(---\h+(?<name>[\w]+)\h+---\h*\n((?<data>.*)\n---\h*)?)?\n?(?<text>.*)$/us', $item, $m);
            $res[] = [
                'name' => $m['name'],
                'data' => Yaml::parse($m['data']),
                'text' => trim($m['text']),
            ];
        }
        
        return new ContentStack($res);
    }


    function getParent()
    {
        $tree = $this->_site->getTree($this->_item['l10n']);
        $link = $this->_item['link'];
        do {
            $link = rtrim(preg_replace('#[^/]+$#', '', $link), '/');
        } while ($link and !isset($tree[$link]));
        return $link ? $this->_site->getItem($tree[$link]) : NULL;
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
        return $res;
    }


    function getLocale()
    {
        return $this->_site->getLocale($this->_item['l10n']);
    }


    function getTemplate()
    {
        return @reset(array_filter([
            @$this->_item['data']['template'],
            @$this->_item['type'] ? substr($this->_item['type'], 1, -1) : NULL,
            'default',
        ]));
    }


    function getName()
    {
        return @$this->_item['data']['name'] ?: $this->_item['name'];
    }


    function getCaption()
    {
        return @$this->_item['data']['caption'] ?: $this->getName();
    }


    function getHref()
    {
        return $this->_site->getRouter()->to($this->_item['href']);
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
