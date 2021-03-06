<?php

namespace Rake\Template;

class Helper
{


    static function cleanup($res)
    {
        /*
        $res = preg_replace('/<!--.+-->/mU', '', $res);     // remove html comments
        */
    		$res = preg_replace('{\r\n?}', "\n", $res);         // standardize newlines
        $res = preg_replace('/^\s+$/mu', '', $res);         // remove blank lines
        #$res = preg_replace('/\h*\n\h*/u', "\n", $res);     // remove spaces from empty lines
        $res = preg_replace('/\h+$/um', '', $res);          // remove trailing spaces
        return $res;
    }


    static function parseLinks(\Twig_Environment $env, $site, $r, $content)
    {
        $res = $content;
        // parse emails
        $res = preg_replace_callback('#\[email(?<hide>:hide)?:(?<email>[^\]]+)\]#iu', function ($m) use ($r) {
            $email = $m['email'];
            if (!empty($m['hide'])) {
                $email = str_rot13($email);
                return '<a href="#" data-mailto="' .$email. '">' .$email. '</a>';
            } else {
                return '<a href="mailto:' .$email. '">' .$email. '</a>';
            }
        }, $res);
        // parse internal links
        $res = preg_replace_callback('#\[(?<filter>{.+})\](\[(?<caption>[^\]]+)\])?#iu', function ($m) use ($env, $r) {
            $filter = \Symfony\Component\Yaml\Yaml::parse($m['filter']);
            $caption = $m['caption'];
            $query = $env->getGlobals()['tree']->pages->filter($filter);
            if ($query->count()) {
                return '<a href="' .$query->getFirst()->getHref(). '">' .$caption. '</a>';
            } else {
                trigger_error('Invalid query: ' . $m['filter']);
                return '<a href="#">' .$caption. '</a>';
            }
        }, $res);
        // parse links
        $res = preg_replace_callback('#\[(?<tag>link:)?(?<ext>ext\w*:)?(?<url>[^\[\]\v]+)\](\[(?<caption>[^\[\]\v]+)\])?(?!\()#iu', function ($m) use ($r) {
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
        }, $res);
        //
        return $res;
    }


    static function parseImages($r, $res)
    {
        $recipe = '\[image:(?<image>[^\]]+)(:(?<w>\d+)(x(?<h>\d+))?)?\]';
        // parse block images
        $res = preg_replace_callback('#^\h*' .$recipe. '(?<caption>.*)?$#iumU', function($m) use ($r) {
            $caption = isset($m['caption']) ? trim($m['caption']) : FALSE;
            return '<figure class="image"'
                .(!empty($m['w']) ? ' style="max-width:' .$m['w']. 'px"' : ''). '>' . self::parseImageFragment($r, $m)
                .(!empty($caption) ? '<figcaption>' .trim($caption). '</figcaption>' : ''). '</figure>';
        }, $res);
        // parse inline images
        $res = preg_replace_callback('#' .$recipe. '#iuU', function($m) use ($r) {
            return self::parseImageFragment($r, $m);
        }, $res);
        //
        return $res;
    }


    static function parseImageFragment($r, $m)
    {
        $src = $m['image'];
        if ('/' != $src{0} and !preg_match('#^\w+://#', $src)) {
            $src = './' . $src;
        }
        return '<img src="'
            .$r->to($src). '"' .(!empty($m['w']) ? ' width="' .$m['w']. '"' : '')
            .(!empty($m['h']) ? ' height="' .$m['h']. '"' : ''). '>';
    }


    static function parseVideos($r, $content)
    {
        return preg_replace_callback('#^\h*\[video:(?<vendor>.*):(?<id>.*)\]\h*$#imU', function($m) use ($r) {
            return '<div class="video"><iframe width="1280" height="720" src="https://www.youtube.com/embed/' .$m['id']. '" frameborder="0" allowfullscreen></iframe></div>';
        }, $content);
    }
}