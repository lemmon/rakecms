<?php

namespace Rake\Template;

use Rake\Router;

class Extension extends \Twig_Extension
{
    private $_router;


    function __construct(Router $router)
    {
        $this->_router = $router;
    }


    function getName()
    {
        return 'rake';
    }


    function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('link_to', function($_) {
                return call_user_func_array([$this->_router, 'to'], func_get_args());
            }),
            new \Twig_SimpleFunction('link_*', function($name, ...$args) {
                return call_user_func_array([$this->_router, 'get' . $name], $args);
            }),
        ];
    }


    function getFilters()
    {
        return [
            new \Twig_SimpleFilter('dump', function($stdin){ dump($stdin); }),
            new \Twig_SimpleFilter('json', function($in) {
                return json_encode(iterator_to_array($in));
            }, ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('tNum', function($number, $dec = 0){ return number_format($number, $dec, ',', ' '); }),
            new \Twig_SimpleFilter('tPrice', function($number, $dec = 2){ return number_format($number, $dec, ',', ' '); }),
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
            new \Twig_SimpleFilter('sp', function($res) {
                $res = preg_replace('/(?<=\b\w)\s(?=\w)/um', '&nbsp;', $res);
                $res = preg_replace('/"(.*)"/sumU', '&bdquo;$1&ldquo;', $res);
                $res = preg_replace('/(?<!!)\-\-(?!\>)/', '&ndash;', $res);
                $res = preg_replace('/\s*\.{3,}/', '&hellip;', $res);
                return $res;
            }, ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('md', function($res) {
                $res = preg_replace('/<!--.+-->/mU', '', $res);
                // image
                $res = Helper::parseImages($this->_router, $res);
                // video
                $res = preg_replace_callback('#^[ \t]*\[video:(?<vendor>.*):(?<id>.*)\]\s*$#mUi', function($m) {
                    return '<div class="video"><iframe width="1280" height="720" src="https://www.youtube.com/embed/' .$m['id']. '" frameborder="0" allowfullscreen></iframe></div>';
                }, $res);
                // link
                $res = Helper::parseLinks($this->_router, $res);
                // markdown
                $res = \Michelf\Markdown::defaultTransform($res);
                return $res;
            }, ['is_safe' => ['html']]),
            new \Twig_SimpleFilter('mdi', function($res, $len = FALSE) {
                // check
                if (FALSE !== $len and $len <= 0) {
                    return '';
                }
                // remove html comments
                $res = preg_replace('/<!--.+-->/mU', '', $res);
                // standardize newlines
        		$res = preg_replace('{\r\n?}', "\n", $res);
                // remove blank lines
                $res = preg_replace('/^\s+$/m', '', $res);
                // remove following paragraphs
                $res = preg_replace("/\n\n.*/", '', $res);
                // link
                $res = Helper::parseLinks($this->_router, $res);
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
                        $res = (new \Tidy)->repairString($res, ['show-body-only' => TRUE], 'utf8');
                    }
                    do {
                        $res = preg_replace('#\s*<(\w+)[^>]*>\s*</\1>\s*#', '', $res, -1, $n);
                        $res = preg_replace('/(\s|&nbsp;)+\w$/u', '', $res);
                    } while ($n);
                    $res .= '&hellip;';
                }
                //
                return $res;
            }, ['is_safe' => ['html']]),
        ];
    }
}
