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

function filter($array, $filters) {
    return array_filter(array_map(function($item) use ($filters) {
        foreach ($filters as $filter => $value) {
            if (!($item = _filter($item, explode('.', $filter), $value))) {
                return FALSE;
            }
        }
        return $item;
    }, $array));
}

function _filter(array $item, array $filter, $value) {
    $current = array_shift($filter);
    if ('*' == $current) {
        return array_filter(array_map(function($item) use ($filter, $value) {
            return _filter($item, $filter, $value);
        }, $item));
    } elseif (array_key_exists($current, $item)) {
        if ($filter) {
            if (is_array($item[$current]) and $res = _filter($item[$current], $filter, $value)) {
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