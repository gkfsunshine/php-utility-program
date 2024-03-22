<?php
namespace App\Utils;

//配置时间的组装
class Time
{
    //获取当前时间
    final static function getTime($timeFormat = 'Y-m-d',$params = [])
    {
        return date($timeFormat,time());
    }

    //时间戳精确到毫秒级
    private static function getMsTime()
    {
        return '000';
    }

    //当前毫秒
    final static function getCurrentMsTime($timeFormat = 'Y-m-d')
    {
        return get_date_to_mesc(self::getTime($timeFormat));
    }

    //获取今天时间戳
    final static function getTodayMkTime()
    {
        $time = [
            'start_time' => mktime(0,0,0,date('m'),date('d'),date('Y')),
            'end_time'   => mktime(0,0,0,date('m'),date('d')+1,date('Y'))
        ];
        $time['start_time'] .= static::getMsTime();
        $time['end_time'] .= static::getMsTime();
        return $time;
    }

    //获取本月时间戳
    final static function getMonthMkTime()
    {
        $time = [
            'start_time' => mktime(0,0,0,date('m'),1,date('Y')),
            'end_time'   => mktime(0,0,0,date('m')+1,1,date('Y'))
        ];
        $time['start_time'] .= static::getMsTime();
        $time['end_time'] .= static::getMsTime();
        return $time;
    }

    //获取本年度时间戳
    final static function getYearMkTime()
    {
        $time = [
            'start_time' => mktime(0,0,0,1,1,date('Y')),
            'end_time'   => mktime(0,0,0,date('m')+1,1,date('Y'))
        ];
        $time['start_time'] .= static::getMsTime();
        $time['end_time'] .= static::getMsTime();
        return $time;
    }

    //获取某个时间的开始结束时间
    final static function getMescByData($dataTime,$spilit = '-')
    {
        $count = substr_count($dataTime,$spilit);
        if($count == 1){ //形如2019-1
            list($year,$month) = explode($spilit,$dataTime);
            $time = [
                'start_time' => mktime(0,0,0,$month,1,$year),
                'end_time'   => mktime(0,0,0,$month+1,1,$year)
            ];
        }elseif($count == 2){
            list($year,$month,$day) = explode($spilit,$dataTime);
            $time = [
                'start_time' => mktime(0,0,0,$month,$day,$year),
                'end_time'   => mktime(0,0,0,$month,$day+1,$year)
            ];
        }else{
            list($year) = explode($spilit,$dataTime);
            $time = [
                'start_time' => mktime(0,0,0,1,1,$year),
                'end_time'   => mktime(0,0,0,1,1,$year+1)
            ];
        }
        $time['start_time'] .= static::getMsTime();
        $time['end_time']   .= static::getMsTime();
        return $time;
    }

    //时间计算 返回时间戳
    final static function getMtTimeAgo($day=30)
    {
        return $day * 86400000;
    }

    //时间戳精确到日 返回时间戳
    final static function getMtTimeToDateMs($ms,$format='Y-m-d')
    {
        return get_date_to_mesc(get_msec_to_mescdate($ms,$format));
    }
}
