<?php

namespace Rake\Template;

class Helper
{


    static function parseLinks($r, $content)
    {
        return preg_replace_callback('#\[(?<tag>link:)?(?<ext>ext\w*:)?(?<url>[\.\S]+)\](\[(?<caption>[^\]]+)\])?#iu', function($m) use ($r) {
            $p = '';
            $url = $m['url'];
            $caption = $m['caption'] ?? $m['url'];
            if (preg_match('/^\w+\./', $url)) {
                $url = 'http://' . $url;
            }
            if ($m['ext']) {
                $p .= ' target="_blank"';
            }
            return '<a' .$p. ' href="' .$url. '">' .$caption. '</a>';
        }, $content);
    }


    static function parseImages($r, $content)
    {
        return preg_replace_callback('#[ \t]*\[image:(?<params>.*)\](?<caption>.*)?#ui', function($m) use ($r) {
            $params = explode(':', $m['params']);
            $src = array_shift($params);
            if ($params and preg_match('/\d*(x\d*)?/', $params[0])) {
                $_ = explode('x', array_shift($params));
                $w = $_[0] ?? NULL;
                $h = $_[1] ?? NULL;
            }
            return '<figure class="image"'
                .(isset($w) ? ' style="max-width:' .$w. 'px"' : ''). '><img src="'
                .$r->to('./' . $src). '"' .(isset($w) ? ' width="' .$w. '"' : '')
                .(isset($h) ? ' height="' .$h. '"' : ''). '>'
                .(isset($m['caption']) ? '<figcaption>' .trim($m['caption']). '</figcaption>' : ''). '</figure>';
        }, $content);

    }
}