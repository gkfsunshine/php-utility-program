<?php
namespace App\Utils;
use App\Services\AdminServer\ManagerService;
use App\Traits\Response;
use Illuminate\Contracts\Encryption\DecryptException;

//加解密算法
class AuthCrypt
{
    protected static $ip      = 'ip';
    protected static $browser = 'browser';
    protected static $os      = 'os';

    protected static $encryKey = 'MkFsh72DSXS';
    /**
     * 加密
     *
     * @param array $encryptData
     */
    final static function encrypt($encryptData = [])
    {
        $encryptData = array_merge($encryptData,self::checkSign());
        \Log::debug('授权加密信息'.json_encode($encryptData));
        return base64_encode(serialize($encryptData));
    }

    /**
     * 解密
     *
     * @param String $decryptData
     * @return array|mixed
     */
    final static function decrypt(String $decryptData)
    {
        try {
            $cryptData =  unserialize(base64_decode($decryptData));
            \Log::debug('授权解密信息'.json_encode($cryptData));
            $checkSign = self::checkSign();
            \Log::debug('授权签名信息'.json_encode($checkSign));
            foreach ($checkSign as $key => $val){
                if(!isset($cryptData[$key]) || $val != $cryptData[$key]){
                    return  self::error('非法');
                }
            }
            return self::success('解密成功',$cryptData);
        } catch (\Exception $e) {
            return  self::error('解密失败');
        }
    }

    /**
     * 需要校验的基础信息
     *
     * @return array
     */
    private static function checkSign()
    {
        return [
            self::$ip         => getClientIp(),
            self::$browser    => ClientInfo::browser(),
            self::$os         => ClientInfo::os()
        ];
    }

    //失败
    private static function error($msg = '',$data=[])
    {
        return [
            'status'     => 0,
            'message'    => $msg,
            'data'       => $data
        ];
    }

    //成功
    private static function success($msg = '',$data=[])
    {
        return [
            'status'     => 1,
            'message'    => $msg,
            'data'       => $data
        ];
    }
}