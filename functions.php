<?php

use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\ValidatorFactory;
use MeiquickLib\Exception\BaseValidateException;
use MeiquickLib\Exception\FormValidateException;
use MeiquickLib\Service\Qiniu\UploadType;
use MeiquickLib\Validators\CommonValidator;


/**
 *
 */
if(!function_exists('removeSpaceChar')){
    function removeSpaceChar($str){
        $str = trim($str);
        $str = preg_replace(['/^\t/','/\t$/'],'',$str);
        $str = preg_replace(['/^\r\n/','/\r\n$/'],'',$str);
        $str = preg_replace(['/^\r/','/\r$/'],'',$str);
        $str = preg_replace(['/^\n/','/\n$/'],'',$str);
        $str = preg_replace(['/^ /','/ $/'],'',$str);
        $str = preg_replace(['/^  /','/  $/'],'',$str);
        $str = preg_replace(['/^\s+/u','/\s+$/u'], '',$str);
        $str = preg_replace(['/^[\s\v'.chr(163).chr(160).']+/','/[\s\v'.chr(163).chr(160).']+$/'],'',$str);
        $str = preg_replace(['/^'.chr(194) . chr(160).'/','/'.chr(194) . chr(160).'$/'], '', $str);

        return trim($str); //返回字符串
    }
}

if(!function_exists('getTimeZoneDate')) {
    function getTimeZoneData($timeoffset=8,$dateformat = 'Y-m-d H:i:s',$timestamp = ''): String
    {
        if(empty($timestamp)) {
            $timestamp=time();
        }

        $result = gmdate($dateformat, $timestamp + $timeoffset * 3600);

        return $result;
    }
}

if(!function_exists('container')) {
    function container()
    {
        return ApplicationContext::getContainer();
    }
}
/**
 * 验证统一调用
 * @param array $data
 * @param $validators
 * @return array
 */
if(!function_exists('validator')) {
    function validator(array $data, $validators, bool $throw = true): array
    {
        if (empty($validators)) {
            return $data;
        }
        $validatorFactory = make(ValidatorFactory::class);
        is_string($validators) && $validators = [$validators];

        foreach ($validators as $item) {
            if (!class_exists($item)) {
                throw new BaseValidateException(10101, $item);
            }
            $validator = make($item);
            if ($validator instanceof CommonValidator) {
                /* @var CommonValidator $validator */
                if ($validator->rules() && $validator->messages()) {
                    $valid = $validatorFactory->make($data, $validator->rules(), $validator->messages());
                    if ($valid->fails()) {
                        if ($throw) {
                            throw new BaseValidateException($valid->errors()->first());
                        } else {
                            throw new FormValidateException($valid->errors()->first(), array_key_first($valid->failed()));
                        }
                    }
                }
                $data = $validator->validate($data);
            }
        }

        return $data;
    }
}

/**
 * 生成验证码字符串
 *
 * @param int|null $length
 * @param string|null $charset
 * @return string
 */
if(!function_exists('getRandCode')) {
    function getRandCode(int $length = 4, string $charset = '123456789'): string
    {
        $phrase = '';
        $chars = str_split($charset);
        for ($i = 0; $i < $length; $i++) {
            $phrase .= $chars[array_rand($chars)];
        }

        return $phrase;
    }
}

//生成验证码字符串
if(!function_exists('randNumber')) {
    function randNumber(int $length = 4, string $charset = '123456789'): string
    {
        $string = '';
        $charLen = strlen($charset) - 1;
        for ($i = 0; $i < $length; $i++) {
            $string .= $charset[mt_rand(0, $charLen)];
        }

        return $string;
    }
}

//生成随机数
if(!function_exists('randString')) {
    function randString(int $len = 6, string $charset = 'abcdefghijkmnpqrstuvwxyz23456789')
    {
        $string = '';
        $charLen = strlen($charset) - 1;
        for ($i = 0; $i < $len; $i++) {
            $string .= $charset[mt_rand(0, $charLen)];
        }

        return $string;
    }
}

/**
 * 验证电话格式
 * @param string $mobile
 * @param string $nationCode
 * @return bool
 */
if(!function_exists('phoneCheck')){
    function phoneCheck(string $mobile, string $nationCode = '86'): bool
    {
        $nationCode = ltrim($nationCode, " +");
        $subject = ($nationCode == '86') ? $mobile : $nationCode.$mobile;
        $pattern = "/^[1][2-9]\d{9}$/";
        if( !preg_match($pattern, $subject) ){
            return false;
        }
        return true;
    }
}

/**
 * 验证品牌名称 - 排除中文
 * @param string $brand
 * @return bool
 */
if (!function_exists('checkBrand')) {
    function checkBrand(string $brand): bool
    {
//    if(!in_array($stateId, [710000, 810000, 820000])){
        $reg = "/[\x{4e00}-\x{9fa5}]+/u";
        if (preg_match($reg, $brand)) {
            return false;
        }
//    }
        return true;
    }
}

/**
 * 根据省id获取区号
 */
if (!function_exists('getAreacode')) {
    function getAreacode (int $stateId): string
    {
        switch ($stateId) {
            case 710000 :
                return '886';
            case 810000 :
                return '852';
            case 820000 :
                return '853';
            default :
                return '86';
        }
    }
}

/**
 * 生成流水号
 */
if (!function_exists('getOutTradeNo')) {
    function getOutTradeNo(): string
    {
        return date('YmdHis') . randNumber(14);
    }
}

/**
 * 验证收件人手机格式
 */
if (!function_exists('checkReceiverTel')) {
    function checkReceiverTel($mobile, int $stateId = 0): bool
    {
        if(in_array($stateId, [710000, 810000, 820000])){
            $reg = "/^\d{8,11}$/";
        } else {
            $reg = "/^[1][2-9]\d{9}$/";
        }
        if (!preg_match($reg, $mobile)) {
            return false;
        }

        return true;
    }
}


/**
 * 判断目录是否存在
 */
if (!function_exists('dirExists')) {
    /**
     * @param string $path 目录路径
     * @return bool
     */
    function dirExists($path)
    {
        $f = true;
        if (file_exists($path) == false) {//创建目录
            if (@mkdir($path, 0777, true) == false) {
                $f = false;
            } else if (chmod($path, 0777) == false) {
                $f = false;
            }
        }

        return $f;
    }
}
/**
 * 返回上传后的url
 */
if (!function_exists('wholeUrl')) {
   function wholeUrl($path, $domain = '', $type = 'public'){
       if (empty($domain)) {
           $domain = $type == 'public' ? MeiquickLib\Service\Qiniu\PublicUpload::instance([UploadType::PIC_COUNTRY_ICON])->domain : MeiquickLib\Service\Qiniu\PrivateUpload::instance([UploadType::IDCARD])->domain;
       }
       return (env('APP_ENV', 'dev') == 'prod' ? 'https://' : 'http://').$domain.'/'.$path;
   }
}


if(!function_exists('getClientIp')){
    /**
     * 获取ip 地址
     * @return mixed|string
     */
    function getClientIp($iToLong = true)
    {
        $ip = swoole_get_local_ip();

        return is_array($ip) && !empty($ip) ? array_pop($ip) : '';
    }
}

/**
 * 返回树形结构数据
 */
if (!function_exists('getTree')) {
    function getTree($list){
        //第一步 构造数据
        $items = [];
        foreach($list as $value){
            $items[$value['id']] = $value;
        }
        $tree = [];
        foreach($items as $key => $value){
            if(isset($items[$value['parent_id']])){
                $items[$value['parent_id']]['child'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }
}

/**
 * 生成mkno
 */
if (!function_exists('generateMkno'))
{
    function generateMkno(int $mkno) : string
    {
        $production = env('TEST_ENV') ? false : true;
        return 'MK233' . str_pad((string)$mkno, 7, '0', STR_PAD_LEFT) . ($production ? 'CN' : 'TS');
    }
}

/**
 * 如果给定的 $condition 结果为true 则抛出异常
 *
 * @param  mixed  $condition
 * @param  array  ...$parameters
 * @return mixed
 */
if (! function_exists('throw_if')) {
    function throw_if($condition, $exception, ...$parameters)
    {
        if ($condition) {
            throw (is_string($exception) ? new $exception(...$parameters) : $exception);
        }
        return $condition;
    }
}


if(!function_exists('privatePictureUrl')){
    /**
     * 获取图片
     * @param $picture
     * @param string $domain
     * @return string
     */
    function privatePictureUrl($picture, $domain = '') {
        $uploader = \MeiquickLib\Service\Qiniu\PrivateUpload::instance([UploadType::IDCARD]);
        $domain = $domain ?: $uploader->domain;
        return $uploader->getImageUrl((env('APP_ENV', 'dev') == 'prod' ? 'https://' : 'http://').$domain.DIRECTORY_SEPARATOR.$picture);
    }
}

if(!function_exists('privateWholeUrl')){
    /**
     * 获取图片
     * @param string $url
     * @return string
     */
    function privateWholeUrl(string $url): string
    {
        $uploader = \MeiquickLib\Service\Qiniu\PrivateUpload::instance([UploadType::IDCARD]);
        return $uploader->getImageUrl($url);
    }
}

if (!function_exists('date_conversion_stamp')) {

    /**
     * TODO 日期转时间戳  不传默认获取当前时间的时间戳
     * @param string $string
     * @return false|int
     */
    function date_conversion_stamp($string = "") {
        $string = $string == "" ? date("Y-m-d H:i:s") : $string;
        return strtotime($string);
    }
}

if (!function_exists('isMkno')) {

    /**
     * 判断单号是否为mkno
     * @param string $string
     * @return bool
     */
    function isMkno($string = ""):bool
    {
        if (preg_match('/^MK[0-9]{9,10}[CN|TS]$/', strtoupper(strval($string)))) {
            return true;
        }
        return false;
    }
}

