<?php

namespace Rake\Entity;

use Lemmon\DataStack;

class Posts extends DataStack
{


    function getRecent($a = NULL, $b = NULL)
    {
        $res = $this->getArray();
        // only past entries
        $res = array_filter($res, function ($item) {
            return date('Ymd', is_numeric($item['created']) ? intval($item['created']) : strtotime($item['created'])) <= date('Ymd');
        });
        // sort by date created
        usort($res, function($a, $b){
            $a = is_numeric($a['created']) ? intval($a['created']) : strtotime($a['created']);
            $b = is_numeric($b['created']) ? intval($b['created']) : strtotime($b['created']);
            return $b <=> $a;
        });
        // slice
        if ($a) {
            $res = $b ? array_slice($res, $a, $b) : array_slice($res, 0, $a);
        }
        //
        return new self($res);
    }


    function getSiblingsTo(AbstractEntity $item, $n)
    {
        $i = 0;
        $data = array_values($this->getArray());
        while ($item->getFile() != $data[$i]->getFile()) {
            $i++;
            if (!isset($data[$i])) return FALSE;
        }
        $data = array_merge(($n > $i ? array_slice($data, 0, $i) : array_slice($data, $i - $n, $n)), array_slice($data, $i + 1, $n));
        $data = array_slice($data, ($n > $i ? floor(count($data) / 2) : round(count($data) / 2)) - 1, $n);
        return $data;
    }
}