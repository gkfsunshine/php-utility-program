<?php

namespace App\Helper;

/**
 * 时间
 */
class Time
{
    /**
     * 格式化时间
     *
     * @param array $param 参数
     * @return string
    */
    final public static function date($param=[])
    {
        $param['timestamp']=array_key_exists('timestamp',$param) ? $param['timestamp'] : null;/*时间戳*/
        $param['millisecondTs']=array_key_exists('millisecondTs',$param) ? $param['millisecondTs'] : null;/*毫秒级 时间戳*/
        $param['microtime']=array_key_exists('microtime',$param) ? $param['microtime'] : null;/*microtime()结果*/
        $param['format']=array_key_exists('format',$param) ? $param['format'] : 'Y-m-d H:i:s';/*格式*/
        $param['addDecimal']=(array_key_exists('addDecimal',$param) && $param['addDecimal']);/*是否加小数*/
        $mct=explode(' ',$param['microtime']!==null ? $param['microtime'] : microtime());
        if($param['timestamp']!==null){
            $mct=[
                '',
                $param['timestamp'],
            ];
        }else if($param['millisecondTs']!==null){
            $tmp=explode('.',(string)($param['millisecondTs']/1000));
            $mct=[
                isset($tmp[1]) ? '0.'.$tmp[1] : '',
                $tmp[0],
            ];
        }
        return date($param['format'],$mct[1]).($param['addDecimal'] ? substr($mct[0],1) : '');
    }

    /**
     * 获取当前时间戳 秒 精确到小数
     *
     * @return string
     */
    final public static function decimalNowTs()
    {
        $mt=explode(' ',microtime());
        return $mt[1].substr($mt[0],1);
    }

    /**
     * 获取当前时间戳 毫秒级
     *
     * @param bool $int 是否整数
     * @return int|float
     */
    final public static function nowMlTs($int=true)
    {
        $result=static::decimalNowTs()*1000;
        return $int ? (int)round($result) : $result;
    }

    /**
     * 获取当前时间戳 微秒级
     *
     * @param bool $int 是否整数
     * @return int|float
     */
    final public static function nowMcTs($int=true)
    {
        $result=static::decimalNowTs()*1000000;
        return $int ? (int)round($result) : $result;
    }

    /**
     * 获取当前毫秒时间戳
     * @return float
     */
    final public static function msectime() {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * 解析秒数
     *
     * @param int|float $second 秒数
     * @return array
     */
    final public static function parseSecond($second=0)
    {
        $cfg2sec=[/*秒数配置*/
            'day'=>86400,/*日*/
            'hour'=>3600,/*小时*/
            'minute'=>60,/*秒*/
        ];
        $d='.';
        $sec=explode($d,(string)$second);
        $result=[
            'day'=>0,
            'hour'=>0,
            'minute'=>0,
            'second'=>$sec[0],/*解析秒数为字符串*/
        ];
        foreach($cfg2sec as $k=>$v){
            if($result['second']>=$v){
                $result[$k]=floor($result['second']/$v);
                $result['second']=$result['second']%$v;
            }
        }
        $result['second'].=$d.$sec[1];
        return $result;
    }

    /**
     * 格式化秒数
     *
     * @param int|float $second 秒数
     * @param array $extParam 扩展参数
     * @return string
     */
    final public static function formatSecond($second=0,$extParam=[])
    {
        $extParam['format']=array_key_exists('format',$extParam) ? $extParam['format'] : '{{:day:}}d,{{:hour:}}h,{{:minute:}}m,{{:second:}}s';/*格式*/
        return Str::getBytpl($extParam['format'],static::parseSecond($second));
    }

}