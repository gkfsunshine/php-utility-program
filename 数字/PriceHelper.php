<?php

declare(strict_types=1);

namespace MeiquickLib\Helpers;

class PriceHelper
{
    /**
     * 统一格式化
     *
     * @param array $param 参数
     * @return string
     */
    final public static function format($param=[]): string
    {
        $param['val']=array_key_exists('val',$param) ? $param['val'] : 0;/*值*/
        $param['round']=array_key_exists('round',$param) ? $param['round'] : true;/*是否round*/
        $param['decimal']=array_key_exists('decimal',$param) ? $param['decimal'] : 2;/*保留小数点后几位*/
        $result=$param['val'];
        if($param['round']){
            $result=number_format(round($result,$param['decimal']),$param['decimal'],'.','');
        }
        return (string)$result;
    }

}