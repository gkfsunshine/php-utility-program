<?php
namespace App\Utils;



//对数字类型的封装
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

    /**
     * 过滤字段串中非正整形字符
     *
     * @param string $string 多个英文逗号隔开
     * @return array
     */
    final static function filterInteger($string = '') : array
    {
        if(empty($string)){
            return [];
        }

        return array_filter(explode(',',$string),function($id){
            return preg_match("/^[1-9][0-9]*$/",$id);
        });
    }
}
