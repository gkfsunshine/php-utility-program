<?php
namespace App\Utils;

use App\Traits\Log;

class Request{
    use Log;

    //日志文件名
    protected static function customLogFileDir()
    {
        return 'Utils/'. \App\Helper\Str::ltrim(str_replace(['\\','/'],'_',get_called_class()),'third_request/request_message');
    }

    /**
     *  请求
     *
     * @param array $params
     * @return array
     */
    final static function ping($params = [])
    {
        $responseFormat = array_key_exists('response_format',$params) ? $params['response_format'] : 'json';
        $logWriteFunc=static::customWriteLogFunc([/*写日志方法*/
            'filename'=>__FUNCTION__,
        ]);
        $logWriteFunc['logWrite']([
            'tag'=>'接口请求参数详细',
            'content'=>[
                'third_request_params' => $params
            ],
        ]);
        $curl = curl_init();
        $curlData = [
            CURLOPT_URL             => $params['url'] ?? '',
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_ENCODING        => '',
            CURLOPT_MAXREDIRS       => 10,
            CURLOPT_TIMEOUT         => $params['time_out'] ?? 30,
            CURLOPT_FOLLOWLOCATION  => true,
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST   => strtoupper($params['method'] ?? 'POST'),
            CURLOPT_POSTFIELDS      => $params['data']??[],
            CURLOPT_HTTPHEADER      => $params['header']??[],
            CURLOPT_SSL_VERIFYPEER  => $params['ssl_verify_peer']??0,
            CURLOPT_SSL_VERIFYHOST  => $params['ssl_verify_host']??0,
            CURLOPT_CAINFO          => $params['cacert_url']??'',
        ];

        if(isset($params['ssl_cert_path']) && isset($params['ssl_key_path'])){
            $fileExt='PEM';
            $curlData = array_merge($curlData,[
                CURLOPT_SSLCERTTYPE => $fileExt,
                CURLOPT_SSLCERT     => $params['ssl_cert_path'],
                CURLOPT_SSLKEY      => $fileExt,
                CURLOPT_SSLKEY      => $params['ssl_key_path']
            ]);
        }
        curl_setopt_array($curl, $curlData);
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);
        unset($curl);
        if ($err) {
            $logWriteFunc['errorLogWrite']([
                'tag'=>'签名参数string',
                'content'=>[
                    'third_request_error' => $err
                ],
            ]);
            return ['status'=>false,'data'=>[],'msg'=>$err];
        }
        if($httpCode != 200){
            return ['status'=>false,'data'=>[],'msg'=>'请求错误！错误码'.$httpCode];
        }
        switch (strtolower($responseFormat)){
            case 'json':
                $data = Str::exJson($response);
                break;
            case 'xml':
                $data = Str::xmlToArr($response);
                break;
            default:
                $data = Str::exJson($response);
        }

        $logWriteFunc['logWrite']([
            'tag'=>'接口请求响应信息',
            'content'=>[
                'third_response_msg' => $data
            ],
        ]);
        return ['status'=>true,'data'=>$data,'msg'=>'success'];
    }
}
