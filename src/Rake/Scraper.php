<?php

namespace Rake;

class Scraper
{


    function __construct()
    {
    }


    function scrape()
    {
        $o = getopt('h', ['help', 'static']);
        
        //
        if (isset($o['h']) or isset($o['help'])) {
            $this->_help();
        } else {
            $this->_scrape();
        }
    }


    private function _help()
    {
        echo "\033[0;33mUsage:", PHP_EOL;
        echo "\033[0;37m  command [options]", PHP_EOL;
        echo PHP_EOL;
        echo "\033[0;33mOptions:", PHP_EOL;
        echo "\033[0;32m  -h, --help\033[0;37m  Display this help message", PHP_EOL;
        echo "\033[0;32m  --static\033[0;37m    Build static html files", PHP_EOL;
        echo "\033[0;37m";
    }


    private function _scrape()
    {
        $t = -microtime(TRUE);
        $site = [];
        $root = BASE_DIR . '/content';

        // go through locales
        foreach (glob($root . '/*') as $i => $item) {
            $i = 0;
            $base = basename($item);
            $locale_id = preg('/[a-z]+(_\w+)?/i', $base)[0];
            if ('_' == $base{0} or !is_dir($item)) {
                continue;
            }
            $site['l10n'][$locale_id] = $locale = [
                'id'      => $locale_id,
                'dir'     => $base,
                'active'  => !('_' == $base{0}),
                'primary' => !(bool)$i,
            ];
            echo "\033[0;33mLocale:\033[0;37m " . $locale_id, PHP_EOL;
            $this->_tree($item, function($file, $link, $ns) use (&$site, $locale, $root, &$i) {
                $path = ($i or $ns) ? $link : '';
                $data = $this->_data($file);
                $locale_id = strtolower(strtr($locale['id'], ['_' => '-']));
                echo " \033[1;30m-\033[0;37m " . $link . ($ns ? " \033[1;30m(\033[0;32m{$ns}\033[1;30m)\033[0;37m" : '') . PHP_EOL;
                if ((!isset($data['state']) or !in_array($data['state'], ['draft', 'hidden'])) and !isset($ns)) {
                    $site['tree'][$locale_id][] = '/' . $path;
                }
                $site['data']['/' . $path] = [
                    'l10n' => $locale['id'],
                    'path' => $path,
                    'href' => $path ? $path . '.html' : ':home',
                    'name' => ucwords(strtr(preg_replace('/^(\d+_)?([^\.]+)\..*$/', '$2', basename($file)), ['_' => ' '])),
                    'file' => substr($file, strlen($root) + 1),
                    'time' => filemtime($file),
                    'data' => $data,
                    'type' => $ns,
                ];
                if ($ns) {
                    $site[$ns][$locale_id][] = '/' . $path;
                }
                $i++;
            });
        }

        // save results
        file_put_contents(BASE_DIR . '/build/site.json', json_encode($site, JSON_PRETTY_PRINT));

        // notice
        echo "\033[0;32mScrape okay \033[1;30m(took " .round((microtime(TRUE) + $t) * 1000, 2). "ms)\033[1;37m" . PHP_EOL;
    }


    // travest pages
    private function _tree($dir, $callback, $level = [], $ns = NULL) {
        $l = strlen($dir) + 1;
        $i = 0;
        $glob = glob($dir . '/*');
        usort($glob, function($a, $b){
            $a = basename($a);
            $b = basename($b);
            if ('@' == $a{0} and '@' != $b{0}) return +1;
            if ('@' == $b{0} and '@' != $a{0}) return -1;
            return strcmp($a, $b);
        });
        foreach($glob as $file) {
            $name = basename($file);
            if ('_' === $name{0}) {
                continue;
            }
            if ('@' != $name{0}) {
                preg_match('/^(?:(\d{1,3})_)?([a-z_]+)(?:\.(\w+))?/', $name, $m);
                $slug = strtolower(strtr($m[2], '_', '-'));
                if (is_dir($file)) {
                    $this->_tree($file, $callback, array_merge($level, [$slug]), $ns);
                } else {
                    $type = strtolower($m[3]);
                    if ('txt' === $type) {
                        $callback($file, join(($i or $ns) ? array_merge($level, [$slug]) : $level, '/'), $ns);
                    } else {
                        continue;
                    }
                }
                $i++;
            } else {
                $slug = substr($name, 1);
                $this->_tree($file, $callback, $level, $name);
            }
        }
    }

    // parse data from page
    private function _data($file) {
        $data = file_get_contents($file);
        $data = @preg('/\-\-\-\s+(.*)\-\-\-/suU', $data)[1];
        $data = \Symfony\Component\Yaml\Yaml::parse($data) ?: [];
        unset($data['number']);
        return $data;
    }

}