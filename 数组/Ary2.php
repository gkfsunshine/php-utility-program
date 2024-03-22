<?php

namespace App\Helper;

/**
 * 数组
 */
class Ary
{
    /**
     * 统计不同
     *
     * @param array $param 参数
     * @return array
     */
    final public static function diff($param=[])
    {
        $param['now']=array_key_exists('now',$param) ? $param['now'] : [];/*当前的*/
        $param['old']=array_key_exists('old',$param) ? $param['old'] : [];/*原来的*/
        $del=$param['old'] ? array_diff($param['old'],$param['now']) : [];/*已删的*/
        $add=$param['now'] ? array_diff($param['now'],$param['old']) : [];/*新增的*/
        $keep=($param['now'] && $param['old']) ? array_intersect($param['old'],$param['now']) : [];/*保留的*/
        return array_map(function($value){
            $value=array_merge($value);/*key重置*/
            $value=array_unique($value);/*唯一*/
            return $value;
        },compact(['del','add','keep']));
    }

    /**
     * 提取数据
     *
     * @param array $param 参数
     * @return array
     */
    final public static function pickData($param=[])
    {
        $param['data']=array_key_exists('data',$param) ? $param['data'] : [];/*数据*/
        $param['sourceData']=array_key_exists('sourceData',$param) ? $param['sourceData'] : [];/*源数据*/
        $param['filterKey']=array_key_exists('filterKey',$param) ? $param['filterKey'] : [];/*过滤的key*/
        $result=[];
        $data=array_merge($param['sourceData'],$param['data']);
        foreach($data as $k=>$v){
            if(!array_key_exists($k,$param['data']) && $param['filterKey'] && in_array($k,$param['filterKey'])){
                continue;
            }
            $result[$k]=$v;
        }
        return $result;
    }

    /**
     * 自定义排序
     *
     * @param array $param 参数
     * @return array
     */
    final public static function customSort($param=[])
    {
        $param['data']=array_key_exists('data',$param) ? $param['data'] : [];/*数据*/
        $param['sortKey']=array_key_exists('sortKey',$param) ? $param['sortKey'] : '';/*排序key*/
        $param['sortValue']=array_key_exists('sortValue',$param) ? $param['sortValue'] : [];/*排序value*/
        $param['returnNoSortedDataMod']=array_key_exists('returnNoSortedDataMod',$param) ? $param['returnNoSortedDataMod'] : '';/*返回没有排序的数据模式*/
        $param['returnNoSortedDataMod']=strtolower($param['returnNoSortedDataMod']);
        $param['sortValue']=array_unique($param['sortValue']);
        $val2ary=[];/*值对应数组*/
        $getUniqueKey=function($val){/*获取唯一key*/
            return 'v_'.(string)$val;
        };
        foreach($param['data'] as $k=>$v){
            $uk=$getUniqueKey($v[($param['sortKey'])]);
            if(!array_key_exists($uk,$val2ary)){
                $val2ary[$uk]=[];
            }
            $val2ary[$uk][]=$v;
        }
        $sortedData=[];/*已排序数据*/
        $sortedUk=[];/*已排序唯一key*/
        foreach($param['sortValue'] as $v){
            $uk=$getUniqueKey($v);
            if(array_key_exists($uk,$val2ary)){
                foreach($val2ary[$uk] as $v2){
                    $sortedData[]=$v2;
                }
                $sortedUk[]=$uk;
            }
        }
        $noSortedData=[];/*没有排序的数据*/
        foreach($val2ary as $uk=>$v){
            if(!in_array($uk,$sortedUk)){
                foreach($v as $v2){
                    $noSortedData[]=$v2;
                }
            }
        }
        switch($param['returnNoSortedDataMod']){
            case 'top':/*置顶*/
                $result=array_merge($noSortedData,$sortedData);
                break;
            case 'bottom':/*置底*/
                $result=array_merge($sortedData,$noSortedData);
                break;
            default:
                $result=$sortedData;
                break;
        }
        return $result;
    }

    /**
     * 交集、唯一
     *
     * @param array $ary
     * @return array
     */
    final public static function intersectUnique(...$ary)
    {
        $result=array_intersect(...$ary);
        $result=array_unique($result);
        return $result;
    }

}