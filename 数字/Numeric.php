<?php


namespace App\Helpers;


class Numeric
{
    /**
     * 保留小数
     *
     * @param int $numeric
     * @param int $few
     * @param $type
     * @return float|int|string
     */
    final static function decimal($numeric=0,$few=2,$type = 1)
    {
        switch($type){
            case 0: $numeric = round($numeric,$few);break;
            case 1: $numeric = sprintf("%.".$few."f",$numeric); break;
            case 2: $numeric = number_format($numeric, $few, '.', ''); break;
            default : $numeric = number_format($numeric, $few, '.', '');
        }
        return (float)$numeric;
    }
}