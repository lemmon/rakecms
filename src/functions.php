<?php

namespace Rake;

use Lemmon\Router\Router;

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
    $site = new Site(new Router);
    $site->getRouter()->match('({link}(.{pageno}).html)', ['link' => '[\w\-/]+', 'pageno' => '\d+'], function($router, string $link = NULL) use ($site) {
        $item = $site->getItem('/' . $link);
        $page = $item->getPage();
        $t = new Template\Dispatcher($site);
        echo($t->render($item->getTemplate(), array_replace($item instanceof Entity\Page ? [] : [strtolower(preg('/[^\\\]+$/', get_class($item))[0]) => $item], [
            'site' => $site,
            'page' => $page,
            'tree' => new Tree($item),
            'data' => new Data($item),
            'i18n' => new I18n($item),
        ])));
    });
    if (!$site->getRouter()->dispatch()) {
        throw new HttpNotFoundException;
    }
}
