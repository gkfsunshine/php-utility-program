<?php declare(strict_types=1);
/**
 *
 * @author apple
 * @date 2020-05-01
 */

namespace MeiquickLib\Lib\Utils;

class Arr
{
    /**
     * 判断是否是一维数组 是true 否false
     *
     * @param array $array
     * @return bool
     */
    public static function isDimensionalArray($array = [])
    {
        return count($array) == count($array, 1) ? true : false;
    }

    /**
     * 统计不同
     *
     * @param array $now 当前的
     * @param array $old 原来的
     * @return array
     */
    public static function diff($now=[],$old=[])
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
