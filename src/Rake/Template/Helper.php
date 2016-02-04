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
}