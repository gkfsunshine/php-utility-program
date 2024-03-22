<?php

namespace App\Helpers;

use App\Helpers\File;

/**
 * 日志
 */
class Log
{
    /**
     * 保存到文件
     *
     * @param array $param 参数
     */
    final public static function saveToFile($param=[])
    {
        $param['path']=array_key_exists('path',$param) ? $param['path'] : '';/*文件路径*/
        $param['content']=array_key_exists('content',$param) ? $param['content'] : '';/*内容*/
        $param['addTimePrefix']=array_key_exists('addTimePrefix',$param) ? $param['addTimePrefix'] : true;/*是否添加时间前缀*/
        $param['saveDaily']=(array_key_exists('saveDaily',$param) && $param['saveDaily']);/*是否按天保存*/
        $param['savedDay']=array_key_exists('savedDay',$param) ? (int)$param['savedDay'] : null;/*保存天数*/
        $now=time();/*当前时间戳*/
        $fileMode=0777;/*文件权限*/
        static $checkedExpiredPath=[];/*检查了是否过期了的文件路径*/
        File::mkdir(dirname($param['path']),$fileMode);/*创建目录*/
        $pathinfo=pathinfo($param['path']);/*文件信息*/
        $filePath=$pathinfo['dirname'].'/'.($param['saveDaily'] ? date('Ymd_',$now) : '').$pathinfo['basename'];/*文件路径*/
        $fileContent=($param['addTimePrefix'] ? date('[Y-m-d H:i:s] ',$now) : '').trim($param['content']).PHP_EOL;/*文件内容*/
        $handle=@fopen($filePath, 'a');
        $checkLockTime=1;/*检查锁次数*/
        $maxCheckLockTime=100000;/*最大检查锁次数*/
        $maxPerCheckWaitMicSec=10;/*单次检查最大等待微秒*/
        $fileLockSuccess=false;/*文件是否成功锁了*/
        while($checkLockTime<=$maxCheckLockTime){
            if(@flock($handle,LOCK_EX|LOCK_NB)){
                $fileLockSuccess=true;
                break;
            }
            $checkLockTime++;
            usleep(mt_rand(1,$maxPerCheckWaitMicSec));
        }
        @fwrite($handle,$fileContent);
        if($fileLockSuccess){
            @flock($handle,LOCK_UN);
        }
        @fclose($handle);
        unset($handle);
        @chmod($filePath,$fileMode);
        if($param['saveDaily'] && $param['savedDay']>0
            && (!$checkedExpiredPath || ($checkedExpiredPath && !in_array($param['path'],$checkedExpiredPath)))
        ){
            if($handle=@opendir($pathinfo['dirname'])){
                while(($file=@readdir($handle))!==false){
                    if(in_array($file,['.','..'])){
                        continue;
                    }
                    preg_match_all('/^(\d+)_([\w\W]+?)$/i',$file,$m);
                    if($m && $m[1] && $m[2]
                        && $m[2][0]===$pathinfo['basename']
                        && date('Ymd',strtotime($m[1][0]))<=date('Ymd',$now-60*60*24*$param['savedDay'])
                    ){/*删除过期文件*/
                        @unlink($pathinfo['dirname'].'/'.$file);
                    }
                }
            }
            @closedir($handle);
            unset($handle);
            $checkedExpiredPath[]=$param['path'];
        }
    }


    final public static function exceptionLogFormat(\Exception $e)
    {
        return [
            'file'=>$e->getFile(),
            'line'=>$e->getLine(),
            'message'=>$e->getMessage()
        ];
    }
}