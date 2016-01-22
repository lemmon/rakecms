<?php

namespace Rake;

//
// preg

function preg($pattern, $subject)
{
    preg_match($pattern, $subject, $m);
    return $m;
}

//
// rake

function rake()
{
    // router
    $router = new Router(@config()['router'] ?: []);
    // display page
    #try {
        $site = new \Rake\Site($router);
        $router->match('({link}(.{pageno}).html)', ['link' => '[\w\-/]+', 'pageno' => '\d+'], function($router, $m) use ($site) {
            $item = $site->getItem('/' . @$m['link'], isset($m['pageno']) ? intval($m['pageno']) : NULL);
            $page = $item->getPage();
            $res = template($router, $item->getTemplate(), array_replace($item instanceof \Rake\Page ? [] : [strtolower(substr(get_class($item), 5)) => $item], [
                'site' => $site,
                'page' => $page,
                'tree' => new \Rake\Tree($item),
                'data' => new \Rake\Data($item),
            ]));
            echo $res;
            exit(1);
        });
        throw new \Rake\HttpNotFoundException;
    #} catch (\Rake\HttpException $e) {
    #    die('-- 404 --');
    #}
}

//
// template

function template($router, $name, $data, $cache = NULL)
{
    // loader
    $loader = new \Twig_Loader_Filesystem(BASE_DIR . '/templates');
    // twig
    $twig = new \Twig_Environment($loader, [
        'cache' => $cache ?? BASE_DIR . '/cache/tpl',
        'auto_reload' => TRUE,
    ]);
    // filters
    $twig->addFilter(new \Twig_SimpleFilter('dump', function($stdin){ dump($stdin); }));
    $twig->addFilter(new \Twig_SimpleFilter('tNum', function($number, $dec = 0){ return number_format($number, $dec, ',', ' '); }));
    $twig->addFilter(new \Twig_SimpleFilter('tPrice', function($number, $dec = 2){ return number_format($number, $dec, ',', ' '); }));
    $twig->addFilter(new \Twig_SimpleFilter('tDate', function($ts, $mask = 'Y/m/d') {
        if (is_string($ts) or !is_numeric($ts)) {
            $ts = strtotime($ts);
        }
        return date($mask, $ts);
    }));
    $twig->addFilter(new \Twig_SimpleFilter('tDateTime', function($ts, $mask = 'Y/m/d H:i') {
        if (is_string($ts) or !is_numeric($ts)) {
            $ts = strtotime($ts);
        }
        return date($mask, $ts);
    }));
    $twig->addFilter(new \Twig_SimpleFilter('md', function($res) use ($router){
        $res = preg_replace('/<!--.+-->/mU', '', $res);
        // image
        $res = preg_replace_callback('#[ \t]*\[image:(?<params>.*)\]\s*#mUi', function($m) use ($router) {
            $params = explode(':', $m['params']);
            $src = array_shift($params);
            if ($params and preg_match('/\d*(x\d*)?/', $params[0])) {
                $_ = explode('x', array_shift($params));
                $w = $_[0] ?? NULL;
                $h = $_[1] ?? NULL;
            }
            return '<div class="image"' .($w ? ' style="max-width:' .$w. 'px"' : ''). '><img src="' .$router->to('./' . $src). '"' .($w ? ' width="' .$w. '"' : ''). '></div>';
        }, $res);
        // video
        $res = preg_replace_callback('#^[ \t]*\[video:(?<vendor>.*):(?<id>.*)\]\s*$#mUi', function($m) use ($router) {
            return '<div class="video"><iframe width="1280" height="720" src="https://www.youtube.com/embed/' .$m['id']. '" frameborder="0" allowfullscreen></iframe></div>';
        }, $res);
        // link
        $res = preg_replace_callback('#\[(?<url>[\.\S]+)\]#mUi', function($m) use ($router) {
            return '<a href="' .$m['url']. '">' .$m['url']. '</a>';
        }, $res);
        // markdown
        $res = \Michelf\Markdown::defaultTransform($res);
        return $res;
    }, ['is_safe' => ['html']]));
    $twig->addFilter(new \Twig_SimpleFilter('mdi', function($res, $len = FALSE) use ($router) {
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
        $res = preg_replace_callback('#\[(?<url>[\.\S]+)\]#mUi', function($m) use ($router) {
            return '<a href="' .$m['url']. '">' .$m['url']. '</a>';
        }, $res);
        // markdown inline
        $res = \Parsedown::instance()->line($res);
        // length
        if ($len and ($l = mb_strlen(strip_tags($res))) > $len) {
            do {
                $res = mb_substr($res, 0, 0 - ($l - $len));
                $res = preg_replace('/<[^<>]+>?$/u', '', $res);
                $res = preg_replace('/(\w+)?$/u', '', $res);
                $res = preg_replace('/[\-:;\.,\s]+$/u', '', $res);
                $res = preg_replace('/\s+\w$/u', '', $res);
                $l = mb_strlen(strip_tags($res));
            } while ($l > $len);
            if (FALSE !== strpos($res, '<')) {
                $res = (new \Tidy)->repairString($res, ['show-body-only' => TRUE], 'utf8');
            }
            do {
                $res = preg_replace('#\s*<(\w+)[^>]*>\s*</\1>\s*#', '', $res, -1, $n);
                $res = preg_replace('/\s+\w$/u', '', $res);
            } while ($n);
            $res .= '&hellip;';
        }
        //
        return $res;
    }, ['is_safe' => ['html']]));
    $twig->addFilter(new \Twig_SimpleFilter('json', function($in){ return json_encode(iterator_to_array($in)); }, ['is_safe' => ['html']]));
    // functions
    $twig->addFunction(new \Twig_SimpleFunction('link_to', function($_) use ($router){ return call_user_func_array([$router, 'to'], func_get_args()); }));
    $twig->addFunction(new \Twig_SimpleFunction('link_*', function($name, ...$args) use ($router){ return call_user_func_array([$router, 'get' . $name], $args); }));
    // render
    return $twig->render($name . '.twig.html', $data);
}