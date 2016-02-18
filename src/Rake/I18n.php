<?php

namespace Rake;

use Lemmon\DataStack,
    Symfony\Component\Yaml\Yaml;

class I18n
{
    private $_page;
    private $_base;
    private $_data;
    private $_plural = '{1}|{2,*;0}';


    function __construct(Entity\AbstractEntity $page)
    {
        $this->_page = $page;
        $this->_base = BASE_DIR . '/content/' . $page->getLocale()['dir'];
        $this->_data = file_exists($_ = $this->_base . '/i18n.yml') ? Yaml::parse($_) : NULL;
    }


    function t($str)
    {
        return $this->_data[$str] ?? $str;
    }


    function tn($str)
    {
        return $this->_data[$str] ?? FALSE;
    }
}