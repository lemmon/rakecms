<?php

namespace Rake\Template;

use Rake\Site as Site;

class Extension extends \Twig_Extension
{
    private $_site;


    function __construct(Site $site)
    {
        $this->_site = $site;
    }


    function getName()
    {
        return 'rake';
    }


    function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('link_to', function($_) {
                return call_user_func_array([$this->_site->getRouter(), 'to'], func_get_args());
            }),
            new \Twig_SimpleFunction('link_*', function($name, ...$args) {
                return call_user_func_array([$this->_site->getRouter(), 'get' . $name], $args);
            }),
            new \Twig_SimpleFunction('placeholder', function($w = NULL, $h = NULL) {
                $img = '';
                $img .= '<svg xmlns="http://www.w3.org/2000/svg"' .($w ? ' width="' .$w. '"' : ''). '' .($h ? ' height="' .$h. '"' : ''). '>';
                $img .= '<rect width="100%" height="100%" fill="#c8c8c8"/>';
                $img .= '</svg>';
                return '<img src="data:image/svg+xml;base64,' .base64_encode($img). '"' .($w ? ' width="' .$w. '"' : ''). '' .($h ? ' height="' .$h. '"' : ''). ' alt="">';
            }, ['is_safe' => ['html']]),
        ];
    }


    function getFilters()
    {
        return [
            new \Twig_SimpleFilter('dump', function($stdin) { \dump($stdin); }),
            new \Twig_SimpleFilter('json', function($in) {
                if (is_array($in)) {
                    return json_encode($in);
                } elseif (is_object($in)) {
                    if (method_exists($in, 'getArray')) {
                        return json_encode($in->getArray());
                    } elseif ($in instanceof \Traversable) {
                        return json_encode(iterator_to_array($in));
                    } else {
                        return json_encode($in);
                    }
                }
            }, ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('num', function($number, ...$args) { return number_format($number, ...$args); }),
            new \Twig_SimpleFilter('tNum', function($number, $dec = 0) { return number_format($number, $dec, ',', ' '); }),
            new \Twig_SimpleFilter('tPrice', function($number, $dec = 2) { return number_format($number, $dec, ',', ' '); }),
            new \Twig_SimpleFilter('tDate', function($ts, $mask = 'Y/m/d') {
                if (is_string($ts) or !is_numeric($ts)) {
                    $ts = strtotime($ts);
                }
                return date($mask, $ts);
            }),
            new \Twig_SimpleFilter('tDateTime', function($ts, $mask = 'Y/m/d H:i') {
                if (is_string($ts) or !is_numeric($ts)) {
                    $ts = strtotime($ts);
                }
                return date($mask, $ts);
            }),
            new \Twig_SimpleFilter('line', function($str) {
                return trim(preg_replace('/\s+/', ' ', $str));
            }),
            new \Twig_SimpleFilter('lines', function($str) {
                return array_filter(preg_split('/\v+/', $str));
            }),
            new \Twig_SimpleFilter('sp', function($res, $x = 1) {
                $res = strval($res);
                $res = Helper::cleanup($res);
                $res = preg_split('/\n{2,}/', $res);
                $res = array_map(function($res) use ($x) {
                    return preg_replace_callback('/<[^>]*>(*SKIP)(*F)|[^<]+/um', function($m) use ($x) { // match anything but html tags
                        $res = $m[0];
                        $res = preg_replace('/\x{00a0}/u', '&nbsp;', $res);                         // nbsp
                        $res = preg_replace('/(\b\w{1,' .$x. '})\s(?=\w)/um', '$1&nbsp;', $res);    // nbsp
                        $res = preg_replace('/"(.*)"/sumU', '&bdquo;$1&ldquo;', $res);              // quotes
                        $res = preg_replace('/(?<![\-!])\-\-(?![\-\>])/', '&ndash;', $res);         // dashes
                        $res = preg_replace('/\s*\.{3,}/', '&hellip;', $res);                       // hellip
                        return $res;
                    }, $res);
                }, $res);
                $res = join("\n\n", $res);
                return $res;
            }, ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('md', function(\Twig_Environment $env, $res) {
                $res = Helper::parseImages($this->_site->getRouter(), $res);
                $res = Helper::parseVideos($this->_site->getRouter(), $res);
                $res = Helper::parseLinks($env, $this->_site, $this->_site->getRouter(), $res);
                // markdown
                $res = \Michelf\Markdown::defaultTransform($res);
                return $res;
            }, ['is_safe' => ['html'], 'needs_environment' => TRUE]),
            new \Twig_SimpleFilter('mdi', function(\Twig_Environment $env, $res, $len = FALSE) {
                // check
                if (FALSE !== $len and $len <= 0) {
                    return '';
                }
                $res = Helper::cleanup($res);
                $res = preg_replace("/\n\n.*/", '', $res);
                // link
                $res = Helper::parseLinks($env, $this->_site, $this->_site->getRouter(), $res);
                // markdown inline
                $res = \Parsedown::instance()->line($res);
                // length
                if ($len and ($l = mb_strlen(html_entity_decode(strip_tags($res)))) > $len) {
                    do {
                        $res = mb_substr($res, 0, 0 - ($l - $len));
                        $res = preg_replace('/$\w+$/', '', $res);
                        $res = preg_replace('/<[^<>]+>?$/u', '', $res);
                        $res = preg_replace('/(\w+)?$/u', '', $res);
                        $res = preg_replace('/(&nbsp;)+$/', '', $res);
                        $res = preg_replace('/[\-:;\.,\s]+$/u', '', $res);
                        $res = preg_replace('/(\s|&nbsp;)+\w$/u', '', $res);
                        $l = mb_strlen(html_entity_decode(strip_tags($res)));
                    } while ($l > $len);
                    if (FALSE !== strpos($res, '<')) {
                        if (class_exists('\Tidy')) {
                          $res = (new \Tidy)->repairString($res, ['show-body-only' => TRUE], 'utf8');
                        } else {
                          $res = strip_tags($res);
                        }
                    }
                    do {
                        $res = preg_replace('#\s*<(\w+)[^>]*>\s*</\1>\s*#', '', $res, -1, $n);
                        $res = preg_replace('/(\s|&nbsp;)+\w$/u', '', $res);
                    } while ($n);
                    $res .= '&hellip;';
                }
                //
                return $res;
            }, ['is_safe' => ['html'], 'needs_environment' => TRUE]),
            new \Twig_SimpleFilter('mdx', function(\Twig_Environment $env, $res) {
                $res = Helper::parseImages($this->_site->getRouter(), $res);
                $res = Helper::parseVideos($this->_site->getRouter(), $res);
                $res = Helper::parseLinks($env, $this->_site, $this->_site->getRouter(), $res);
                // markdown
                $res = \Michelf\MarkdownExtra::defaultTransform($res);
                return $res;
            }, ['is_safe' => ['html'], 'needs_environment' => TRUE]),
        ];
    }
}
