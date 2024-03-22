<?php

namespace App\Helpers;

/**
 * 数组
 */
class Ary
{
    /**
     * 自定义按照某个健值排序
     *
     * @param array $array
     * @param array $sortKey
     * @return array
     */
    final public static function arraySortByCustomKey($array=[],$sortKey=[])
    {
        $arr=[];
        foreach ($sortKey as $k){
            if(isset($array[$k])){
                $arr[]=$array[$k];
            }
        }

        return $arr;
    }


    /**
     * 统计不同
     *
     * @param array $now 当前的
     * @param array $old 原来的
     * @return array
     */
    final public static function diff($now=[],$old=[])
    {
        $del=$old ? array_diff($old,$now) : [];/*已删的*/
        $new=$now ? array_diff($now,$old) : [];/*新增的*/
        $keep=($now && $old) ? array_intersect($old,$now) : [];/*保留的*/
        return array_map(function($value){
            $value=array_merge($value);/*key重置*/
            $value=array_unique($value);/*唯一*/
            return $value;
        },compact(['del','new','keep']));
    }
}