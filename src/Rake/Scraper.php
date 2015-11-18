<?php

namespace Rake;

class Scraper
{


    function __construct()
    {
    }


    function scrape()
    {
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
            echo 'Locale: ' . $locale_id . PHP_EOL;
            $this->_tree($item, function($file, $link, $type) use (&$site, $locale, $root, &$i) {
                $path = ($i or $type) ? $link : '';
                $data = $this->_data($file);
                $locale_id = strtolower(strtr($locale['id'], ['_' => '-']));
                echo ' - ' . $link . ($type ? " ({$type})" : '') . PHP_EOL;
                if ((!isset($data['state']) or !in_array($data['state'], ['draft', 'hidden'])) and !isset($type)) {
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
                    'type' => $type,
                ];
                if ($type) {
                    $site[$type][$locale_id][] = '/' . $path;
                }
                $i++;
            });
        }

        // save results
        file_put_contents(BASE_DIR . '/build/site.json', json_encode($site, JSON_PRETTY_PRINT));

        // notice
        echo 'Parse Ok' . PHP_EOL;
    }


    // travest pages
    private function _tree($dir, $callback, $level = [], $ns = NULL) {
        $l = strlen($dir) + 1;
        $i = 0;
        foreach(glob($dir . '/*') as $file) {
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