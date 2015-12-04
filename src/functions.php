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
// filter

function _filter($item, array $filter, $value) {
    if (!is_array_like($item)) {
        return FALSE;
    }
    $current = array_shift($filter);
    if ('*' == $current) {
        return array_filter(array_map(function($item) use ($filter, $value) {
            return _filter($item, $filter, $value);
        }, $item));
    } elseif (isset($item[$current])) {
        if ($filter) {
            if (is_array_like($item[$current]) and $res = _filter($item[$current], $filter, $value)) {
                $item[$current] = $res;
                return $item;
            } else {
                return FALSE;
            }
        } elseif ($item[$current] == $value) {
            return $item;
        } else {
            return FALSE;
        }
    } else {
        return FALSE;
    }
}

//
// arrays

function is_array_like($item)
{
    return is_array($item) or (is_object($item) and $item instanceof \ArrayAccess);
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

function template($router, $name, $data)
{
    // loader
    $loader = new \Twig_Loader_Filesystem(BASE_DIR . '/templates');
    // twig
    $twig = new \Twig_Environment($loader, [
        'cache' => BASE_DIR . '/cache/tpl',
        'auto_reload' => TRUE,
    ]);
    // filters
    $twig->addFilter(new \Twig_SimpleFilter('dump', function($stdin){ dump($stdin); }));
    $twig->addFilter(new \Twig_SimpleFilter('tNum', function($number, $dec = 0){ return number_format($number, $dec, ',', ' '); }));
    $twig->addFilter(new \Twig_SimpleFilter('tDateTime', function($ts){ return date('Y/m/d H:i', strtotime($ts)); }));
    $twig->addFilter(new \Twig_SimpleFilter('md', function($res) use ($router){
        $res = preg_replace('/<!--.+-->/mU', '', $res);
        // image
        $res = preg_replace_callback('#^[ \t]*\[image:(?<src>.*)\]\s*$#mUi', function($m) use ($router){
            return '<div class="image"><img src="' .$router->to('./' . $m['src']). '"></div>';
        }, $res);
        // video
        $res = preg_replace_callback('#^[ \t]*\[video:(?<vendor>.*):(?<id>.*)\]\s*$#mUi', function($m) use ($router){
            return '<div class="video"><iframe width="1280" height="720" src="https://www.youtube.com/embed/' .$m['id']. '" frameborder="0" allowfullscreen></iframe></div>';
        }, $res);
        // markdown
        $res = \Michelf\Markdown::defaultTransform($res);
        return $res;
    }, ['is_safe' => ['html']]));
    $twig->addFilter(new \Twig_SimpleFilter('json', function($in){ return json_encode(iterator_to_array($in)); }, ['is_safe' => ['html']]));
    // functions
    $twig->addFunction(new \Twig_SimpleFunction('link_to', function($_) use ($router){ return call_user_func_array([$router, 'to'], func_get_args()); }));
    $twig->addFunction(new \Twig_SimpleFunction('link_*', function($name, ...$args) use ($router){ return call_user_func_array([$router, 'get' . $name], $args); }));
    // render
    return $twig->render($name . '.twig.html', $data);
}
