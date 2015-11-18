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