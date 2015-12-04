<?php

namespace Rake;

class Posts extends DataStack
{


    function getRecent($a = NULL, $b = NULL)
    {
        $res = $this->getArray();
        // sort by date created
        usort($res, function($a, $b){
            $a = strtotime($a['created']);
            $b = strtotime($b['created']);
            if ($a == $b) return 0;
            return ($a > $b) ? -1 : 1;
        });
        // slice
        if ($a) {
            $res = $b ? array_slice($res, $a, $b) : array_slice($res, 0, $a);
        }
        //
        return new self($res);
    }


    function getSiblingsTo(AbstractItem $item, $n)
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