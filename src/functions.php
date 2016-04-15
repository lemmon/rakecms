<?php

namespace Rake;

use Lemmon\Router\Router;

//
// fucntions

function preg($pattern, $subject)
{
    preg_match($pattern, $subject, $m);
    return $m;
}

function parse_content(string $c)
{
    $c = trim($c);
	$c = preg_replace('{\r\n?}', "\n", $c);
    $c = preg_split('/^(?=---\h+[\w\.]+\h+---)/ums', $c);
    $c = array_filter(array_map(function($item) {
        if (preg_match('/^(---(\h+(?<name>[\w\.]+)\h+---)?\h*\n((?<data>.*)\n---\h*)?)?\n?(?<text>.*)$/us', $item, $m)) {
            return [
                'name' => $m['name'],
                'data' => \Symfony\Component\Yaml\Yaml::parse($m['data']),
                'text' => trim($m['text']),
            ];
        }
    }, $c));
    return parse_content_ns($c);
}

function parse_content_ns(array &$c, string $ns = '', array $res = [])
{
    $i = 0;
    while ($c) {
        $name = $c[0]['name'];
        if ($ns == $name and 0 == $i) {
            $res[] = new Entity\ContentChunk(...array_values(array_replace(array_shift($c), ['name' => $name ? preg('/\w+$/', $name)[0] : ''])));
        } elseif (empty($ns)) {
            $res[] = parse_content_ns($c, preg("/^(\w+)/", $name)[0]);
        } elseif (preg_match("/{$ns}\./", $name, $m)) {
            if (0 == $i) {
                $res[] = new Entity\ContentChunk(preg('/\w+$/', $name)[0]);
            }
            $res[] = parse_content_ns($c, preg("/^{$ns}\.(\w+)/", $name)[0]);
        } else {
            return new Entity\ContentStack($res);
        }
        $i++;
    }
    return new Entity\ContentStack($res);
}

//
// rake

function rake(string $env = NULL)
{
    $site = new Site($env);
    $site->dispatch(function ($s, $p) {
        print($p->render());
        exit(0);
    });
}
