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
            $t = new \Rake\Template\Dispatcher($router);
            echo($t->render($item->getTemplate(), array_replace($item instanceof \Rake\Entity\Page ? [] : [strtolower(preg('/[^\\\]+$/', get_class($item))[0]) => $item], [
                'site' => $site,
                'page' => $page,
                'tree' => new \Rake\Tree($item),
                'data' => new \Rake\Data($item),
            ])));
            exit(1);
        });
        throw new \Rake\HttpNotFoundException;
    #} catch (\Rake\HttpException $e) {
    #    die('-- 404 --');
    #}
}
