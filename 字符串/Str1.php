<?php

namespace App\Helper;

/**
 * 字符串
 */
class Str
{
    /**
     * 从左边去掉指定的字符
     *
     * @param string $str 原字符串
     * @param string $mask 要去掉的字符
     * @return string
     */
    final public static function ltrim($str,$mask='')
    {
        $strLen=(int)(mb_strlen($str));
        $maskLen=(int)(mb_strlen($mask));
        return mb_substr($str,($maskLen>0 && mb_strpos($str,$mask)===0 && $maskLen<=$strLen) ? $maskLen : 0);
    }

    /**
     * 从右边去掉指定的字符
     *
     * @param string $str 原字符串
     * @param string $mask 要去掉的字符
     * @return string
     */
    final public static function rtrim($str,$mask='')
    {
        $strLen=(int)(mb_strlen($str));
        $maskRpos=mb_strrpos($str,$mask);
        $maskLen=(int)(mb_strlen($mask));
        return mb_substr($str,0,($mask && $maskRpos!==false && $maskRpos+$maskLen===$strLen && $maskLen<=$strLen) ? $maskRpos : $strLen);
    }

    /**
     * 从左边匹配
     */
    final public static function leftMatch($param=[])
    {
        $param['haystack']=array_key_exists('haystack',$param) ? $param['haystack'] : '';/*要检索的字符*/
        $param['needle']=array_key_exists('needle',$param) ? $param['needle'] : '';/*要匹配的字符*/
        $param['needleSuffix']=array_key_exists('needleSuffix',$param) ? $param['needleSuffix'] : [];/*要匹配的字符后缀数组*/
        $result=[
            'match'=>false,/*是否匹配*/
            'matchNeedle'=>'',/*匹配到的字符*/
        ];
        $matchNeedle=[];/*匹配到的字符*/
        $doCatchNeedle=function($haystack,$searchNeedle) use(&$matchNeedle){/*执行匹配字符*/
            if(mb_strpos($haystack,$searchNeedle)===0 && !in_array($searchNeedle,$matchNeedle)){
                $matchNeedle[]=$searchNeedle;
            }
        };
        foreach($param['needleSuffix'] as $v){/*匹配后缀*/
            $doCatchNeedle($param['haystack'],$param['needle'].$v);/*加后缀*/
            $doCatchNeedle($param['haystack'],static::rtrim($param['needle'],$v));/*去后缀*/
            foreach($param['needleSuffix'] as $v2){/*换后缀*/
                $doCatchNeedle($param['haystack'],static::rtrim($param['needle'],$v).$v2);
            }
        }
        if(mb_strpos($param['haystack'],$param['needle'])===0 && !in_array($param['needle'],$matchNeedle)){/*整个匹配*/
            $matchNeedle[]=$param['needle'];
        }
        if($matchNeedle){
            $result['match']=true;
            foreach($matchNeedle as $v){
                if(mb_strlen($v)>=mb_strlen($result['matchNeedle'])){
                    $result['matchNeedle']=$v;
                }
            }
        }
        return $result;
    }

    /**
     * 获取 guid
     *
     * @return string
     */
    final public static function guid()
    {
        $charid=strtoupper(md5(uniqid(mt_rand(),true)));
        $hyphen=chr(45);/*-号*/
        $uuid=chr(123)/*{号*/
        .substr($charid,0,8).$hyphen
        .substr($charid,8,4).$hyphen
        .substr($charid,12,4).$hyphen
        .substr($charid,16,4).$hyphen
        .substr($charid,20,12)
        .chr(125);/*}号*/
        return $uuid;
    }

    /**
     * 获取唯一id
     *
     * @param bool $addTs 是否加时间戳
     * @return string
     */
    final public static function uniqueId($addTs=false)
    {
        $guid=static::guid();/*guid*/
        $max=mt_getrandmax();
        $min=(int)pow(10,(strlen($max)-1));
        $rand=mt_rand($min,$max);/*随机数*/
        $ts=microtime();//
        $ts=explode(' ',$ts);
        $ts=$ts[1].$ts[0];
        $uniqid=uniqid('',true);/*uniqid*/
        $ukey=$guid.$rand.$uniqid;
        if($addTs){/*加时间戳*/
            $ukey.=$ts;
        }
        $ukey=str_replace(array('{','-','}','.'),'',$ukey);
        return strtolower($ukey);
    }

    /**
     * 首字母大写
     *
     * @param string $str 字符串
     * @param string $dm 分隔符
     * @return string
     */
    final public static function ucfirst($str,$dm='_')
    {
        $ary=explode($dm,$str);
        foreach($ary as $k=>$v){
            $ary[$k]=ucfirst($v);
        }
        $str=implode('',$ary);
        return $str;
    }

    /**
     * json转ary
     *
     * @param array|string $json json
     * @return array
     */
    final public static function json2ary($json)
    {
        if(is_array($json)){
            return $json;
        }else{
            $result=@json_decode($json,true);
            return is_array($result) ? $result : [];
        }
    }

    /**
     * 截取分隔符分割的字符串，限制个数
     *
     * @param string $str 字符串
     * @param int $offset 开始位置
     * @param int $length 长度
     * @param string $dm 分隔符
     * @return string
     */
    final public static function sliceDmStr($str,$offset=0,$length=1,$dm=',')
    {
        $str=array_slice(explode($dm,$str),$offset,$length);
        $str=implode($dm,$str);
        return $str;
    }

    /**
     * 分割分隔符分割的字符串
     *
     * @param string $str 字符串
     * @param mixed $filter 过滤匿名函数
     * @param string $dm 分隔符
     * @return array
     */
    final public static function explodeDmStr($str,$filter=null,$dm=',')
    {
        $result=[];
        if($str){
            $tmp=explode($dm,$str);
            foreach($tmp as $v){
                $v=trim($v);
                $pick=($filter!==null ? $filter($v) : true);
                if($pick){
                    $result[]=$v;
                }
            }
        }
        return $result;
    }

    /**
     * 通过模板获取
     *
     * @param string $tpl 模版
     * @param array $data 数据
     * @return string
     */
    final public static function getBytpl($tpl,$data)
    {
        $ss=[];
        $sr=[];
        foreach($data as $k=>$v){
            $s='{{:'.$k.':}}';
            if(strpos($tpl,$s)!==false){
                $ss[]=$s;
                $v=is_array($v) ? json_encode($v) : (string)$v;
                $sr[]=$v;
            }
        }
        return str_replace($ss,$sr,$tpl);
    }

    /**
     * 去掉空格
     *
     * @param string $str 字符串
     * @return string
     */
    final public static function cutBlank($str='')
    {
        $str=preg_replace('/\s+/','',$str);
        $str=str_replace(urldecode('%C2%A0'),'',$str);
        $str=str_replace(urldecode('%E2%80%8B'),'',$str);
        $str=str_replace(urldecode('%E2%80%AC'),'',$str);
        $str=str_replace(urldecode('%E2%80%AD'),'',$str);
        return $str;
    }

    /**
     * 是否正整数
     *
     * @param mixed $val 值
     * @retrun bool
     */
    final public static function isPositiveInteger($val)
    {
        return preg_match('/^[1-9][0-9]*$/',(string)$val)>0 && $val>0;
    }

    /**
     * 获取基本名
     *
     * @param array $param 参数
     * @return string
     */
    final public static function basename($param=[])
    {
        $param['val']=array_key_exists('val',$param) ? $param['val'] : '';/*值*/
        $param['dm']=array_key_exists('dm',$param) ? $param['dm'] : '/';/*分隔符*/
        $sp=mb_strrpos($param['val'],$param['dm']);/*开始位置*/
        $sp=$sp>0 ? $sp+1 : 0;
        $result=mb_substr($param['val'],$sp);
        return $result;
    }

    /**
     * 分割成数组
     *
     * @param string $string 字符串
     * @param int $splitLength 分割长度
     * @param string $encoding 字符编码
     * @return array
     */
    final public static function split(string $string='',int $splitLength=1,string $encoding='UTF-8')
    {
        if(function_exists('mb_str_split')){
            return mb_str_split($string,$splitLength,$encoding);
        }
        $len=mb_strlen($string,$encoding);
        $result=[];
        for($i=0;$i<$len;$i+=$splitLength){
            $result[]=mb_substr($string,$i,$splitLength,$encoding);
        }
        return $result;
    }

    /**
     * 加密
     *
     * @param array $param 参数
     * @return string
     */
    final public static function encrypt(array $param=[])
    {
        $param['content']=array_key_exists('content',$param) ? (string)$param['content'] : '';/*内容*/
        $param['placeholder']=array_key_exists('placeholder',$param) ? $param['placeholder'] : '*';/*加密后的占位符*/
        $callback=array_key_exists('callback',$param) ? $param['callback'] : function(){};/*每个符号回调*/
        if($param['content']===''){
            return $param['placeholder'];
        }
        $contentAry=static::split($param['content']);
        foreach($contentAry as $key=>$item){
            $res=$callback($key,$item);
            $res['encrypt']=array_key_exists('encrypt',$res) ? $res['encrypt'] : true;/*是否加密*/
            $res['show']=array_key_exists('show',$res) ? $res['show'] : true;/*是否展示*/
            if($res['encrypt']){
                $contentAry[$key]=$param['placeholder'];
            }
            if(!$res['show']){
                unset($contentAry[$key]);
            }
        }
        return implode('',$contentAry);
    }

}