<?php

namespace App\Helpers;

use App\Helpers\Base\TencentCloudApi;
use TencentCloud\Tmt\V20180321\Models\TextTranslateBatchRequest;
use TencentCloud\Tmt\V20180321\Models\TextTranslateRequest;
use TencentCloud\Common\Exception\TencentCloudSDKException;

/**
 * 腾讯云文本翻译
 */
class TencentCloudTextTranslate extends TencentCloudApi
{
    /**
     * 翻译
     *
     * @param string $content 内容
     * @param string $to 目标语言
     * @param string $from 原语言
     * @return string
     */
    final public static function trans($content,$to='en',$from='auto',$defaultContent=true)
    {
        $baseExLogWriteParam=[/*执行日志写入参数*/
            'filename'=>'text_translate_trans',
        ];
        static $request;
        static $allResult=[];/*所有结果*/
        $allResKey=$content.$to.$from;
        if(array_key_exists($allResKey,$allResult)){/*翻译同一个内容时，直接返回上一次查询结果*/
            return $allResult[$allResKey];
        }
        $allResult[$allResKey]=$defaultContent?$content:'';
        try{
            if(!$request){
                $request=new TextTranslateRequest();
                $request->ProjectId=static::config('TextTranslateRequest.ProjectId');
            }
            $request->Source=$from;
            $request->Target=$to;
            $request->SourceText=$content;
            $response=static::TmtClient()->TextTranslate($request);
            if(isset($response->TargetText)){
                $allResult[$allResKey]=$response->TargetText;
            }
        }catch(TencentCloudSDKException $e){
            static::exLogWrite(array_merge($baseExLogWriteParam,[
                'tag'=>'error',
                'content'=>[
                    'requestId'=>$e->getRequestId(),
                    'errorCode'=>$e->getErrorCode(),
                    'message'=>$e->getMessage(),
                ],
            ]));
        }catch(\Exception $e){
            static::exLogWrite(array_merge($baseExLogWriteParam,[
                'tag'=>'error',
                'content'=>[
                    'file'=>$e->getFile(),
                    'line'=>$e->getLine(),
                    'message'=>$e->getMessage(),
                ],
            ]));
        }
        return $allResult[$allResKey];
    }

    /**
     * 翻译批量
     *
     * @param array $contentList
     * @param string $to
     * @param string $from
     * @return array|mixed
     */
    final public static function transBatch($contentList=[],$to='en',$from='auto')
    {
        static $request;
        try{
            if(!$request){
                $request=new TextTranslateBatchRequest();
                $request->ProjectId=static::config('TextTranslateRequest.ProjectId');
            }
            $request->Source=$from;
            $request->Target=$to;
            $request->SourceTextList=$contentList;
            $response=static::TmtClient()->TextTranslateBatch($request);

            return $response->TargetTextList;
        }catch(\Exception $e){
            return $contentList;
        }
    }
}