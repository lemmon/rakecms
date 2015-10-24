<?php

require __DIR__ . '/../src/Rake/Router.php';

$r = new \Rake\Router;

var_dump($r);
var_dump([
    $r->getRoot(),
    $r->getHome(),
    $r->getSelf(),
    $r->getRoute(),
    $r->to('/'),
    $r->to('./'),
    $r->to('./foo/bar'),
    $r->to('/foo/bar'),
    $r->to('foo/bar'),
]);

exit(1);