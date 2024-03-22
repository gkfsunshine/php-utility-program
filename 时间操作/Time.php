<?php


namespace App\Utils;


class Time
{
    /**
     * 时间戳转日期
     *
     * @param $timestamp
     * @return false|string
     */
    public final static function customTimeToDate($timestamp){
        return date('Y-m-d H:i:s',$timestamp);
    }

    //毫秒时间戳
    public final static function getMicrotime(){
        list($msec, $sec) = explode(' ', microtime());
        return sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }

    /**
     * 以步长为单位将两个时间段分组 默认7 day
     *
     * @param array $params
     * @return array
     */
    public static final function customDateStepToArr($params=[])
    {
        $params['start_time']=array_key_exists('start_time',$params) ? $params['start_time'] : '';/*开始时间*/
        $params['end_time']=array_key_exists('end_time',$params) ? $params['end_time'] : '';/*结束时间*/
        $params['step']=array_key_exists('step',$params) ? $params['step'] : 7;/*时间步长*/
        $params['step_type']=array_key_exists('step_type',$params) ? $params['step_type'] : 'day';/*时间步长单位*/
        $params['format']=array_key_exists('format',$params) ? $params['format'] : 'Y-m-d H:s:i';/*日期格式*/
        $params['group_num']=array_key_exists('group_num',$params) ? $params['group_num'] : 1;/*分组单位*/
        $params['is_complete_group']=array_key_exists('is_complete_group',$params) ? $params['is_complete_group'] : true;/*是否将组填充完整*/
        $tmp[]=$params['start_time'];
        $now=$params['start_time'];
        $maxTime=1000;$i=0;/*不允许死循环*/
        do{
            $i++;if($i>$maxTime)break;
            $now=date($params['format'],strtotime('+'.$params['step'].' '.$params['step_type'],strtotime($now)));
            if($now>=$params['end_time']){
                $tmp[]=$params['end_time'];
            }else{
                $tmp[]=$now;
            }
        }while($now<$params['end_time']);
        $arr=[];
        if($params['group_num']>1){
            $count=count($tmp);
            for ($i=0;$i<$count/$params['group_num']; $i++) {
                $arr[$i]=array_slice($tmp, $params['group_num'] * $i, $params['group_num']);
            }
            if($params['is_complete_group']){
                if(count($arr[$i-1])<$params['group_num']){
                    for ($y=0;count($arr[$i-1])<$params['group_num'];$y++){
                        array_push($arr[$i-1],date($params['format'],time()));
                    }
                }
            }
        }else{
            $arr=$tmp;
        }

        return $arr;
    }

    /**
     * 日期转时间戳
     *
     * @param $date
     * @return false|float|int|string
     */
    public static final function customDateToMicro($date)
    {
        return self::customTime([
            'timestamp' => 'date_to_micro',
            'data_time' => $date
        ]);
    }

    /**
     * 时间戳转日期
     *
     * @param $date
     * @return false|float|int|string
     */
    public static final function customMicroToDate($micro,$format='Y-m-d H:i:s')
    {
        return self::customTime([
            'timestamp' => 'micro_to_date',
            'time_format' => $format,
            'micro_time' => $micro
        ]);
    }

    /**
     * 当前-时间 y-m-d
     *
     * @param string $format
     * @return false|float|string
     */
    public static final function customTimeDate($format = 'Y-m-d H:i:s')
    {
        return self::customTime([
            'timestamp' => 'date',
            'time_format' => $format
        ]);
    }

    /**
     * 当前-毫秒时间戳 ms
     *
     * @return false|float|string
     */
    public static final function customTimesMills()
    {
        return self::customTime([
            'timestamp' => 'millisecond'
        ]);
    }

    /**
     * 当前-秒时间戳 s
     *
     * @return false|float|string
     */
    public static final function customTimestamp()
    {
        return self::customTime();
    }

    /**
     * 原生s时间戳
     *
     * @return false|float|string
     */
    public static final function customTimestampRaw()
    {
        return self::customTime([
            'timestamp' => 'millisecond_raw'
        ]);
    }

    /**
     * 时间
     *
     * @param array $params
     * @return false|float|string
     */
    private static final function customTime($params=[])
    {
        $timestamp = array_key_exists('timestamp',$params) ? $params['timestamp'] : 'second';/*时间戳类型*/
        $dateFormat = array_key_exists('time_format',$params) ? $params['time_format'] : 'Y-m-d H:i:s';/*时间格式*/
        $customDateTime = array_key_exists('data_time',$params) ? $params['data_time'] : date('Y-m-d H:i:s');/*自定义的日期*/
        $customMicrotime = array_key_exists('micro_time',$params) ? $params['micro_time'] : time();/*自定义的时间戳*/
        list($msc,$sec) = explode(' ',microtime());
        switch (strtolower($timestamp)){
            case 'date':/*获取时间 Y-m-d*/
                $customTime = date($dateFormat,$sec);
                break;
            case 'millisecond':/*毫秒时间戳 ms*/
                $customTime = (int)((floatval($sec) + floatval($msc)) *  1000);
                break;
            case 'millisecond_raw':/*原生毫秒时间戳 raw*/
                $customTime = floatval($sec) + floatval($msc);
                break;
            case 'date_to_micro':/*日期转时间戳*/
                $customTime = strtotime($customDateTime);
                break;
            case 'micro_to_date':/*时间戳转日期*/
                $customTime = date($dateFormat,$customMicrotime);
                break;
            default:/*时间戳*/
                $customTime =  (int)$sec;
        }

        return $customTime;
    }
}