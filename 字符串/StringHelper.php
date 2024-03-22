<?php

declare(strict_types=1);

namespace MeiquickLib\Helpers;

/**
 * 字符串
 */
class StringHelper
{
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

    /**
     * 简单加密
     *
     * @param string $string 字符串
     * @param int $encryptNum 加密位数
     * @param string $placeholder 加密占位符
     * @return string
     */
    final public static function simpleEncrypt(string $string='',int $encryptNum=4,string $placeholder='*'): string
    {
        $encryptNum=max(1,$encryptNum);
        $strLen=mb_strlen($string);/*字符长度*/
        if($strLen<$encryptNum){
            return str_repeat($placeholder,$encryptNum);
        }
        $startEncryptNo=$strLen%2 ? ($strLen-1)/2 : ($strLen-2)/2;/*开始加密位号*/
        $endEncryptNo=$startEncryptNo+$encryptNum-1;/*结束加密位号*/
        return StringHelper::encrypt([
            'content'=>$string,
            'placeholder'=>$placeholder,
            'callback'=>function($key,$item) use($startEncryptNo,$endEncryptNo){
                $n=$key+1;
                return [
                    'encrypt'=>($n>=$startEncryptNo && $n<=$endEncryptNo),
                ];
            }
        ]);
    }

    /**
     * array->json
     *
     * @param array $params
     * @return false|string
     */
    final static function echoJson($params = []): string
    {
        return json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}